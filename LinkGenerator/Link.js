// Get the share button from the DOM
const sharebutton = document.getElementById("sharebutton");

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