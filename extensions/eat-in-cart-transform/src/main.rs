use shopify_function::prelude::*;
use std::process;

pub mod cart_transform_run;

#[typegen("schema.graphql")]
pub mod schema {
    #[query(
        "src/cart_transform_run.graphql",
        custom_scalar_overrides = {
            "CartTransformRunInput.cart.lines.cost.amountPerQuantity.amount" => ::std::string::String,
        }
    )]
    pub mod cart_transform_run {}
}

fn main() {
    log!("Please invoke a named export.");
    process::abort();
}
