var script = document.createElement("script");
script.src = 'https://chat-assets.frontapp.com/v1/chat.bundle.js';

// add it to the end of the head section of the page (could change 'head' to 'body' to add it to the end of the body section instead)

document.body.appendChild(script);
script.addEventListener('load', () => {
    let frontConfig = Statamic.$config.get('front');

    window.FrontChat(
        'init',
        {
            chatId: frontConfig.chatId,
            email: frontConfig.email,
            userHash: frontConfig.hash,
            useDefaultLauncher: true
        }
    );
});
