use super::schema;
use super::schema::cart_transform_run::cart_transform_run_input::cart::lines::Merchandise;
use shopify_function::prelude::*;
use shopify_function::Result;

const ORDER_TYPE_EAT_IN: &str = "eat_in";
const ORDER_TYPE_TAKEAWAY: &str = "takeaway";
const BEHAVIOUR_DYNAMIC: &str = "dynamic";
const FALLBACK_UPLIFT_PERCENT: &str = "20";
const TITLE_PREFIX: &str = "Eat In: ";
const ATTRIBUTE_EAT_IN_FOR: &str = "_eat_in_for";
const ATTRIBUTE_UPLIFT_PERCENT: &str = "_eat_in_uplift_percent";

/// A non-negative decimal held exactly as `mantissa / 10^scale`, so money and
/// percentage maths never goes through binary floating point.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
struct ExactDecimal {
    mantissa: u128,
    scale: u32,
}

impl ExactDecimal {
    /// Maximum number of fractional digits accepted; keeps the arithmetic well inside `u128`.
    const MAX_SCALE: u32 = 12;

    /// Parses a plain decimal string such as `"2.95"`, `"20"` or `"17.50"`.
    /// Negative numbers, exponents and anything else malformed return `None`.
    fn parse(raw: &str) -> Option<Self> {
        let trimmed = raw.trim();
        let unsigned = trimmed.strip_prefix('+').unwrap_or(trimmed);
        let (whole, fraction) = match unsigned.split_once('.') {
            Some((whole, fraction)) => (whole, fraction),
            None => (unsigned, ""),
        };

        if whole.is_empty() && fraction.is_empty() {
            return None;
        }
        if !whole.bytes().all(|byte| byte.is_ascii_digit())
            || !fraction.bytes().all(|byte| byte.is_ascii_digit())
        {
            return None;
        }

        let fraction = fraction.trim_end_matches('0');
        let scale = u32::try_from(fraction.len()).ok()?;
        if scale > Self::MAX_SCALE {
            return None;
        }

        let mut mantissa: u128 = 0;
        for byte in whole.bytes().chain(fraction.bytes()) {
            mantissa = mantissa
                .checked_mul(10)?
                .checked_add(u128::from(byte - b'0'))?;
        }

        Some(Self { mantissa, scale })
    }

    fn is_positive(&self) -> bool {
        self.mantissa > 0
    }

    /// Canonical string form without trailing zeros, e.g. `"20"` or `"17.5"`.
    fn to_canonical_string(self) -> String {
        if self.scale == 0 {
            return self.mantissa.to_string();
        }
        let digits = format!(
            "{:0>width$}",
            self.mantissa,
            width = self.scale as usize + 1
        );
        let split_at = digits.len() - self.scale as usize;
        format!("{}.{}", &digits[..split_at], &digits[split_at..])
    }
}

/// Eat-in surcharge for one unit, in minor units (pence):
/// `round_half_up(unit_price * uplift_percent / 100)` to 2 decimal places.
///
/// In hundredths that is `unit_price * uplift_percent`, i.e.
/// `(price_mantissa * percent_mantissa) / 10^(price_scale + percent_scale)`.
fn surcharge_minor_units(unit_price: ExactDecimal, uplift_percent: ExactDecimal) -> Option<u128> {
    let numerator = unit_price.mantissa.checked_mul(uplift_percent.mantissa)?;
    let denominator = 10u128.checked_pow(unit_price.scale + uplift_percent.scale)?;

    numerator
        .checked_add(denominator / 2)
        .map(|rounded| rounded / denominator)
}

fn minor_units_to_decimal(minor_units: u128) -> Decimal {
    Decimal(minor_units as f64 / 100.0)
}

/// Settings stored as JSON in the `$app:eat-in.config` metafield on the CartTransform.
#[derive(Debug, PartialEq)]
struct EatInConfig {
    eat_in_charge_variant_id: String,
    default_uplift_percent: ExactDecimal,
    default_order_type: String,
}

impl EatInConfig {
    /// Returns `None` when the metafield is absent, not an object, or has no charge variant id.
    fn from_json(json: &JsonValue) -> Option<Self> {
        let JsonValue::Object(fields) = json else {
            return None;
        };

        let eat_in_charge_variant_id = match fields.get("eatInChargeVariantId") {
            Some(JsonValue::String(id)) if !id.trim().is_empty() => id.trim().to_string(),
            _ => return None,
        };

        let fallback_uplift = ExactDecimal::parse(FALLBACK_UPLIFT_PERCENT)?;
        let default_uplift_percent = match fields.get("defaultUpliftPercent") {
            Some(JsonValue::Number(number)) => ExactDecimal::parse(&number.to_string()),
            Some(JsonValue::String(text)) => ExactDecimal::parse(text),
            _ => None,
        }
        .unwrap_or(fallback_uplift);

        let default_order_type = match fields.get("defaultOrderType") {
            Some(JsonValue::String(order_type)) => normalise_order_type(order_type),
            _ => None,
        }
        .unwrap_or(ORDER_TYPE_TAKEAWAY)
        .to_string();

        Some(Self {
            eat_in_charge_variant_id,
            default_uplift_percent,
            default_order_type,
        })
    }
}

/// Maps a raw `_order_type` value to a known order type, or `None` if unrecognised/empty.
fn normalise_order_type(raw: &str) -> Option<&'static str> {
    match raw.trim().to_ascii_lowercase().as_str() {
        ORDER_TYPE_EAT_IN => Some(ORDER_TYPE_EAT_IN),
        ORDER_TYPE_TAKEAWAY => Some(ORDER_TYPE_TAKEAWAY),
        _ => None,
    }
}

#[shopify_function]
fn cart_transform_run(
    input: schema::cart_transform_run::CartTransformRunInput,
) -> Result<schema::CartTransformRunResult> {
    let no_operations = schema::CartTransformRunResult { operations: vec![] };

    let Some(config) = input
        .cart_transform()
        .config()
        .and_then(|metafield| EatInConfig::from_json(metafield.json_value()))
    else {
        return Ok(no_operations);
    };

    let order_type = input
        .cart()
        .order_type()
        .and_then(|attribute| {
            attribute
                .value()
                .and_then(|value| normalise_order_type(value))
        })
        .unwrap_or(config.default_order_type.as_str());

    if order_type != ORDER_TYPE_EAT_IN {
        return Ok(no_operations);
    }

    let operations = input
        .cart()
        .lines()
        .iter()
        .filter_map(|line| eat_in_expand_operation(line, &config))
        .collect();

    Ok(schema::CartTransformRunResult { operations })
}

/// Builds the `lineExpand` for a dynamic line, or `None` when the line must be left alone.
fn eat_in_expand_operation(
    line: &schema::cart_transform_run::cart_transform_run_input::cart::Lines,
    config: &EatInConfig,
) -> Option<schema::Operation> {
    let Merchandise::ProductVariant(variant) = line.merchandise() else {
        return None;
    };

    if variant.id() == &config.eat_in_charge_variant_id {
        return None;
    }

    let product = variant.product();
    let is_dynamic = product.behaviour().is_some_and(|metafield| {
        metafield
            .value()
            .trim()
            .eq_ignore_ascii_case(BEHAVIOUR_DYNAMIC)
    });
    if !is_dynamic {
        return None;
    }

    let unit_price_text = line.cost().amount_per_quantity().amount();
    let unit_price = ExactDecimal::parse(unit_price_text).filter(ExactDecimal::is_positive)?;

    let uplift_percent = product
        .uplift()
        .and_then(|metafield| ExactDecimal::parse(metafield.value()))
        .filter(ExactDecimal::is_positive)
        .unwrap_or(config.default_uplift_percent);

    let surcharge = surcharge_minor_units(unit_price, uplift_percent).filter(|pence| *pence > 0)?;
    let unit_price_decimal = Decimal::try_from(unit_price_text.clone()).ok()?;

    Some(schema::Operation::LineExpand(schema::LineExpandOperation {
        cart_line_id: line.id().clone(),
        title: Some(format!("{TITLE_PREFIX}{}", product.title())),
        image: None,
        price: None,
        expanded_cart_items: vec![
            schema::ExpandedItem {
                merchandise_id: variant.id().clone(),
                quantity: 1,
                price: Some(fixed_price_per_unit(unit_price_decimal)),
                attributes: None,
            },
            schema::ExpandedItem {
                merchandise_id: config.eat_in_charge_variant_id.clone(),
                quantity: 1,
                price: Some(fixed_price_per_unit(minor_units_to_decimal(surcharge))),
                attributes: Some(vec![
                    schema::AttributeOutput {
                        key: ATTRIBUTE_EAT_IN_FOR.to_string(),
                        value: variant.id().clone(),
                    },
                    schema::AttributeOutput {
                        key: ATTRIBUTE_UPLIFT_PERCENT.to_string(),
                        value: uplift_percent.to_canonical_string(),
                    },
                ]),
            },
        ],
    }))
}

fn fixed_price_per_unit(amount: Decimal) -> schema::ExpandedItemPriceAdjustment {
    schema::ExpandedItemPriceAdjustment {
        adjustment: schema::ExpandedItemPriceAdjustmentValue::FixedPricePerUnit(
            schema::ExpandedItemFixedPricePerUnitAdjustment { amount },
        ),
    }
}

#[cfg(test)]
mod tests {
    use super::*;
    use serde_json::{json, Value};
    use shopify_function::run_function_with_input;

    const CHARGE_VARIANT: &str = "gid://shopify/ProductVariant/999";

    fn config() -> Value {
        json!({
            "eatInChargeVariantId": CHARGE_VARIANT,
            "defaultUpliftPercent": 20,
            "defaultOrderType": "takeaway"
        })
    }

    fn line(
        line_id: u32,
        variant_id: &str,
        price: &str,
        title: &str,
        behaviour: Option<&str>,
        uplift: Option<&str>,
    ) -> Value {
        json!({
            "id": format!("gid://shopify/CartLine/{line_id}"),
            "quantity": 2,
            "cost": { "amountPerQuantity": { "amount": price } },
            "merchandise": {
                "__typename": "ProductVariant",
                "id": variant_id,
                "product": {
                    "title": title,
                    "behaviour": behaviour.map(|value| json!({ "value": value })),
                    "uplift": uplift.map(|value| json!({ "value": value })),
                }
            }
        })
    }

    fn dynamic_line(line_id: u32, price: &str) -> Value {
        line(
            line_id,
            &format!("gid://shopify/ProductVariant/{line_id}"),
            price,
            "Cheese Sandwich",
            Some("dynamic"),
            None,
        )
    }

    fn payload(order_type: Option<&str>, lines: Vec<Value>, config: Option<Value>) -> String {
        json!({
            "cart": {
                "orderType": order_type.map(|value| json!({ "value": value })),
                "lines": lines,
            },
            "cartTransform": {
                "config": config.map(|value| json!({ "jsonValue": value })),
            }
        })
        .to_string()
    }

    fn run(payload: String) -> schema::CartTransformRunResult {
        run_function_with_input(cart_transform_run, &payload).expect("function should run")
    }

    fn expected_expand(
        line_id: u32,
        variant_id: &str,
        title: &str,
        unit_price: f64,
        surcharge: f64,
        uplift: &str,
    ) -> schema::Operation {
        schema::Operation::LineExpand(schema::LineExpandOperation {
            cart_line_id: format!("gid://shopify/CartLine/{line_id}"),
            title: Some(format!("Eat In: {title}")),
            image: None,
            price: None,
            expanded_cart_items: vec![
                schema::ExpandedItem {
                    merchandise_id: variant_id.to_string(),
                    quantity: 1,
                    price: Some(fixed_price_per_unit(Decimal(unit_price))),
                    attributes: None,
                },
                schema::ExpandedItem {
                    merchandise_id: CHARGE_VARIANT.to_string(),
                    quantity: 1,
                    price: Some(fixed_price_per_unit(Decimal(surcharge))),
                    attributes: Some(vec![
                        schema::AttributeOutput {
                            key: "_eat_in_for".to_string(),
                            value: variant_id.to_string(),
                        },
                        schema::AttributeOutput {
                            key: "_eat_in_uplift_percent".to_string(),
                            value: uplift.to_string(),
                        },
                    ]),
                },
            ],
        })
    }

    /// Returns the eat-in charge amount of the single expand operation, as its serialised string.
    fn surcharge_for_price(price: &str, uplift: Option<&str>) -> Option<String> {
        let result = run(payload(
            Some("eat_in"),
            vec![line(
                1,
                "gid://shopify/ProductVariant/1",
                price,
                "Item",
                Some("dynamic"),
                uplift,
            )],
            Some(config()),
        ));
        match result.operations.as_slice() {
            [] => None,
            [schema::Operation::LineExpand(expand)] => {
                match &expand.expanded_cart_items[1].price.as_ref()?.adjustment {
                    schema::ExpandedItemPriceAdjustmentValue::FixedPricePerUnit(fixed) => {
                        Some(fixed.amount.to_string())
                    }
                }
            }
            other => panic!("unexpected operations: {other:?}"),
        }
    }

    #[test]
    fn eat_in_mixed_cart_expands_only_dynamic_lines() {
        let result = run(payload(
            Some("eat_in"),
            vec![
                dynamic_line(1, "3.95"),
                line(
                    2,
                    "gid://shopify/ProductVariant/2",
                    "2.50",
                    "Cola",
                    Some("standard"),
                    None,
                ),
                line(
                    3,
                    "gid://shopify/ProductVariant/3",
                    "1.20",
                    "Banana",
                    Some("zero"),
                    None,
                ),
                line(
                    4,
                    "gid://shopify/ProductVariant/4",
                    "1.00",
                    "Mystery",
                    None,
                    None,
                ),
            ],
            Some(config()),
        ));

        assert_eq!(
            result,
            schema::CartTransformRunResult {
                operations: vec![expected_expand(
                    1,
                    "gid://shopify/ProductVariant/1",
                    "Cheese Sandwich",
                    3.95,
                    0.79,
                    "20",
                )],
            }
        );
    }

    #[test]
    fn eat_in_expands_every_dynamic_line() {
        let result = run(payload(
            Some("eat_in"),
            vec![dynamic_line(1, "3.95"), dynamic_line(2, "2.95")],
            Some(config()),
        ));

        assert_eq!(result.operations.len(), 2);
        assert_eq!(
            result.operations[1],
            expected_expand(
                2,
                "gid://shopify/ProductVariant/2",
                "Cheese Sandwich",
                2.95,
                0.59,
                "20",
            )
        );
    }

    #[test]
    fn takeaway_cart_has_no_operations() {
        let result = run(payload(
            Some("takeaway"),
            vec![dynamic_line(1, "3.95")],
            Some(config()),
        ));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn missing_order_type_uses_takeaway_default() {
        let result = run(payload(None, vec![dynamic_line(1, "3.95")], Some(config())));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn missing_order_type_uses_eat_in_default() {
        let mut eat_in_default = config();
        eat_in_default["defaultOrderType"] = json!("eat_in");

        let result = run(payload(
            None,
            vec![dynamic_line(1, "3.95")],
            Some(eat_in_default),
        ));

        assert_eq!(result.operations.len(), 1);
    }

    #[test]
    fn missing_default_order_type_falls_back_to_takeaway() {
        let result = run(payload(
            None,
            vec![dynamic_line(1, "3.95")],
            Some(json!({ "eatInChargeVariantId": CHARGE_VARIANT })),
        ));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn product_uplift_overrides_default() {
        let result = run(payload(
            Some("eat_in"),
            vec![line(
                1,
                "gid://shopify/ProductVariant/1",
                "4.00",
                "Hot Pie",
                Some("dynamic"),
                Some("15.0"),
            )],
            Some(config()),
        ));

        assert_eq!(
            result.operations,
            vec![expected_expand(
                1,
                "gid://shopify/ProductVariant/1",
                "Hot Pie",
                4.0,
                0.6,
                "15",
            )]
        );
    }

    #[test]
    fn zero_or_invalid_product_uplift_uses_default() {
        assert_eq!(
            surcharge_for_price("4.00", Some("0")),
            Some("0.8".to_string())
        );
        assert_eq!(
            surcharge_for_price("4.00", Some("abc")),
            Some("0.8".to_string())
        );
    }

    #[test]
    fn default_uplift_comes_from_config() {
        let mut custom_default = config();
        custom_default["defaultUpliftPercent"] = json!(17.5);

        let result = run(payload(
            Some("eat_in"),
            vec![dynamic_line(1, "2.00")],
            Some(custom_default),
        ));

        assert_eq!(
            result.operations,
            vec![expected_expand(
                1,
                "gid://shopify/ProductVariant/1",
                "Cheese Sandwich",
                2.0,
                0.35,
                "17.5",
            )]
        );
    }

    #[test]
    fn surcharge_rounds_half_up_without_float_drift() {
        assert_eq!(surcharge_for_price("2.95", None), Some("0.59".to_string()));
        assert_eq!(surcharge_for_price("1.99", None), Some("0.4".to_string()));
        // 0.10 * 25% = 0.025 exactly -> rounds half up to 0.03 (binary floats give 0.02).
        assert_eq!(
            surcharge_for_price("0.10", Some("25")),
            Some("0.03".to_string())
        );
        // 1.15 * 10% = 0.115 -> 0.12 (1.15 * 0.1 in f64 is 0.11499999...).
        assert_eq!(
            surcharge_for_price("1.15", Some("10")),
            Some("0.12".to_string())
        );
        // 0.05 * 20% = 0.01
        assert_eq!(surcharge_for_price("0.05", None), Some("0.01".to_string()));
    }

    #[test]
    fn line_with_zero_surcharge_is_skipped() {
        // 0.02 * 20% = 0.004 -> rounds to 0.00.
        assert_eq!(surcharge_for_price("0.02", None), None);
    }

    #[test]
    fn line_with_zero_price_is_skipped() {
        assert_eq!(surcharge_for_price("0.0", None), None);
    }

    #[test]
    fn missing_config_has_no_operations() {
        let result = run(payload(Some("eat_in"), vec![dynamic_line(1, "3.95")], None));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn config_without_charge_variant_has_no_operations() {
        let result = run(payload(
            Some("eat_in"),
            vec![dynamic_line(1, "3.95")],
            Some(json!({ "defaultUpliftPercent": 20, "defaultOrderType": "eat_in" })),
        ));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn eat_in_charge_line_is_not_expanded() {
        let result = run(payload(
            Some("eat_in"),
            vec![line(
                1,
                CHARGE_VARIANT,
                "0.79",
                "Eat-in charge",
                Some("dynamic"),
                None,
            )],
            Some(config()),
        ));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn custom_product_line_is_not_expanded() {
        let result = run(payload(
            Some("eat_in"),
            vec![json!({
                "id": "gid://shopify/CartLine/1",
                "quantity": 1,
                "cost": { "amountPerQuantity": { "amount": "5.00" } },
                "merchandise": { "__typename": "CustomProduct" }
            })],
            Some(config()),
        ));

        assert!(result.operations.is_empty());
    }

    #[test]
    fn exact_decimal_parsing() {
        let parse = |raw: &str| ExactDecimal::parse(raw).map(ExactDecimal::to_canonical_string);

        assert_eq!(parse("20"), Some("20".to_string()));
        assert_eq!(parse("17.50"), Some("17.5".to_string()));
        assert_eq!(parse("0.05"), Some("0.05".to_string()));
        assert_eq!(parse(" 3.95 "), Some("3.95".to_string()));
        assert_eq!(parse(".5"), Some("0.5".to_string()));
        assert_eq!(parse("-1.00"), None);
        assert_eq!(parse("1e3"), None);
        assert_eq!(parse(""), None);
        assert_eq!(parse("."), None);
    }
}
