<script src='https://chat-assets.frontapp.com/v1/chat.bundle.js'></script>
<script>
    window.FrontChat(
        'init',
        {
            chatId: '{{ $chatId }}',
            email: '{{ $email }}',
            userHash: '{{ $hash }}',
            useDefaultLauncher: true
        }
    );
</script>
