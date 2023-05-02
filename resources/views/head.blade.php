<div>
    @if ($configured)
        <script src='https://chat-assets.frontapp.com/v1/chat.bundle.js'></script>
        <script>
            window.FrontChat(
                'init', {
                    chatId: '{{ $chatId }}',
                    email: '{{ $user["email"] ?? null }}',
                    name: '{{ $user["name"] ?? null }}',
                    userHash: '{{ $user["hash"] ?? null }}',
                    useDefaultLauncher: true
                }
            );
        </script>
    @endif
</div>
