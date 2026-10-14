Statamic.booted(() => {
    const frontConfig = Statamic.$config.get('front');

    if (! frontConfig) {
        return;
    }

    const script = document.createElement('script');
    script.src = 'https://chat-assets.frontapp.com/v1/chat.bundle.js';
    script.addEventListener('load', () => window.FrontChat('init', { ...frontConfig, useDefaultLauncher: true }));
    document.body.appendChild(script);
});
