export const createHostedSession = async () => {
    try {
        const response = await fetch('/app/mantle/create-session', {
            method: 'POST',
        });

        return await response.json();
    } catch (error) {
        console.error('Error creating session:', error);
        throw error;
    }
}