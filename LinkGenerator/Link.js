// Get the share button from the DOM
const sharebutton = document.getElementById("sharebutton");
const codeInput = document.getElementById("codeInput");

// Function to generate a random slug
function generateSlug(length = 16) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let slug = '';
    for (let i = 0; i < length; i++) {
        slug += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return slug;
}

// Function to generate a new link and log it to the console
function generateLink() {
    const slug = generateSlug();
    const link = `http://pastebin-knockoff.42web.io/${slug}`;
    console.log(link);

    // Get the text from the input field
    const text = codeInput.value;

    // Send an AJAX request to the PHP script
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_text.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(`slug=${slug}&text=${text}`);

    // Create a new element under the share button
    const linkElement = document.createElement("p");
    linkElement.textContent = `Link: ${link}`;
    sharebutton.parentNode.appendChild(linkElement);
}

// Add an event listener to the share button if it exists
if (sharebutton) {
    sharebutton.addEventListener("click", generateLink);
} else {
    console.error("De sharebutton met de id 'sharebutton' bestaat niet.");
}