document.addEventListener('DOMContentLoaded', function() {
    // Define your example instructions
    const examples = {
        instructions_example1: "You are a helpful AI assistant. Answer customer questions using the knowledge base provided. If you don't know the answer, politely say so.",
        instructions_example2: "You are an expert support agent for our WordPress site. Always greet the user, answer their questions, and offer to help with common tasks.",
        instructions_example3: "You are a friendly chatbot. Use simple language, be concise, and always try to guide the user to the right resource or action."
    };

    // Attach click listeners to each example link
    Object.keys(examples).forEach(function(id) {
        const link = document.getElementById(id);
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('instructions').value = examples[id];
            });
        }
    });
});