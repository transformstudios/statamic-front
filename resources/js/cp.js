Statamic.booted(() => {
    let frontConfig = Statamic.$config.get('front');

    if (frontConfig && frontConfig.chatId) {
        var script = document.createElement("script");
        script.src = 'https://chat-assets.frontapp.com/v1/chat.bundle.js';

        // add it to the end of the head section of the page (could change 'head' to 'body' to add it to the end of the body section instead)
        document.body.appendChild(script);
        script.addEventListener('load', () => {
            window.FrontChat(
                'init',
                {
                    chatId: frontConfig.chatId,
                    email: frontConfig.email,
                    name: frontConfig.name,
                    userHash: frontConfig.hash,
                    useDefaultLauncher: true
                }
            );
        });
    }
});
