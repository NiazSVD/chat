<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>Messenger</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f4f9;
        }
        .chat-container {
            display: flex;
            height: 90vh;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }
        .user-list {
            width: 25%;
            background: #f9f9f9;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }
        .user-list h4 {
            padding: 15px;
            text-align: center;
            background: #007bff;
            color: #fff;
            margin: 0;
        }
        .user-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .user-list li {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .user-list li:hover {
            background-color: #e8f0ff;
        }
        .user-list li.active {
            background-color: #dbeafe; 
            font-weight: bold;
        }
        .chat-box {
            width: 75%;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: #007bff;
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        .chat-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fafafa;
        }
        .chat-message {
            display: flex;
            margin-bottom: 10px;
        }
        .sent {
            justify-content: flex-end;
        }
        .received {
            justify-content: flex-start;
        }
        .message {
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 70%;
        }
        .sent .message {
            background-color: #007bff;
            color: white;
        }
        .received .message {
            background-color: #e2e2e2;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex-grow: 1;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 10px;
            outline: none;
        }
        .chat-input button {
            border-radius: 20px;
            margin-left: 10px;
        }
        .badge {
            background: red;
            border-radius: 10px;
            color: white;
            font-size: 12px;
            padding: 3px 7px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="chat-container">
        <!-- User List -->
        <div class="user-list">
            <h4>{{ auth()->user()->name }}</h4>

            <ul id="userList">
                @foreach($users as $user)
                    @php
                        $unreadCount = \App\Models\Message::where('user_id', $user->id)
                            ->where('receiver_id', auth()->id())
                            ->where('is_read', false)
                            ->count();
                        $avatar = $user->image_show;
                    @endphp
                    <li data-user-id="{{ $user->id }}" 
                        data-user-name="{{ $user->name }}"
                        data-user-avatar="{{ asset($avatar) }}"
                        class="d-flex align-items-center p-2 border-bottom" 
                        style="cursor:pointer;">
                        
                        <img src="{{ asset($avatar) }}" alt="{{ $user->name }}" class="rounded-circle mr-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <span>{{ $user->name }}</span>
                        </div>

                        @if($unreadCount > 0)
                            <span class="badge badge-danger ml-auto">{{ $unreadCount }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>

        </div>

        <!-- Chat Box -->
        <div class="chat-box">
            <div class="chat-header" id="chatUserName">Select a user</div>
            <!-- typing status -->
            <div id="typingStatus" class="text-muted pl-3" style="height:20px;"></div>

            <div class="chat-content" id="chatContent">
                <div class="text-muted text-center mt-5">Select a user to start chatting</div>
            </div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type your message..." disabled>
                <button class="btn btn-primary" id="sendMessageBtn" disabled>Send</button>
            </div>
        </div>
    </div>

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

{{-- <script>
    // Laravel Echo setup
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ env('PUSHER_APP_KEY') }}",
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        forceTLS: true
    });

    let selectedUserId = null;
    let selectedUserName = '';

    $(document).ready(function () {
    let selectedUserId = null;
    let selectedUserName = '';

    //  User select event
    $('#userList').on('click', 'li', function () {

        $('#userList li').removeClass('active');
        $(this).addClass('active');

        selectedUserId = $(this).data('user-id');
        selectedUserName = $(this).data('user-name');
        let selectedUserAvatar = $(this).data('user-avatar');

        //chat box disable
        $('#chatInput').prop('disabled', false);
        $('#sendMessageBtn').prop('disabled', false);

        
        $('#chatUserName').html(`
            <div class="d-flex align-items-center">
                <img src="${selectedUserAvatar}" 
                     alt="${selectedUserName}" 
                     class="rounded-circle mr-2" 
                     width="40" height="40">
                <span>${selectedUserName}</span>
            </div>
        `);

        // Chat content reset
        $('#chatContent').html('');

        //  Unread badge remove
        $(this).find('.badge').remove();

        //  Message history load
        $.get(`/messages/${selectedUserId}`, function (messages) {
            messages.forEach(msg => {
                appendMessage(
                    msg.message,
                    msg.user_id == {{ auth()->id() }} ? 'sent' : 'received'
                );
            });
        });

        //  Mark messages as read
        $.post('/mark-as-read', {
            _token: "{{ csrf_token() }}",
            sender_id: selectedUserId
        });
    });

    //  Send message (button)
    $('#sendMessageBtn').click(function () {
        let text = $('#chatInput').val();
        if (text.trim() === '' || !selectedUserId) return;

        $.post('/send-message', {
            _token: "{{ csrf_token() }}",
            message: text,
            receiver_id: selectedUserId
        });

        appendMessage(text, 'sent');
        $('#chatInput').val('');
    });

   
    // $('#chatInput').keypress(function (e) {
    //     if (e.which === 13 && !e.shiftKey) {
    //         e.preventDefault();
    //         $('#sendMessageBtn').click();
    //     }
    // });

    /// typing status 
    $('#chatInput').keypress(function (e) {

        // ENTER চাপলে (shift ছাড়া)
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();

            // Send message
            $('#sendMessageBtn').click();

            // টাইপ বন্ধ হয়েছে — typing false পাঠাও
            isTyping = false;
            sendTypingStatus(false);
        }
    });



    // Real-time message receive
    // logged in user id
    const myId = {{ auth()->id() }};

    // Create channel
    const channel = window.Echo.private('chat.' + myId);

    // Listen for new messages
    channel.listen('.message.sent', (e) => {

        let senderId = e.user_id;

        if (selectedUserId == senderId) {
            appendMessage(e.message, 'received');

            $.post('/mark-as-read', {
                _token: "{{ csrf_token() }}",
                sender_id: senderId
            });

        } else {
            let badge = $(`#userList li[data-user-id="${senderId}"] .badge`);
            if (badge.length) {
                badge.text(parseInt(badge.text()) + 1);
            } else {
                $(`#userList li[data-user-id="${senderId}"]`)
                    .append('<span class="badge badge-danger ml-auto">1</span>');
            }
        }
    });


    // Listen for typing status
    channel.listen('.user.typing', (e) => {

        if (selectedUserId == e.sender_id) {
            if (e.is_typing) {
                $('#typingStatus').text('typing...');
            } else {
                $('#typingStatus').text('');
            }
        }
    });


    // append message
    function appendMessage(text, type) {
        let msgDiv = $('<div>').addClass('chat-message ' + type);
        let msgText = $('<div>').addClass('message').text(text);
        msgDiv.append(msgText);
        $('#chatContent').append(msgDiv);
        $('#chatContent').scrollTop($('#chatContent')[0].scrollHeight);
    }
});

    // Listen for typing status
    channel.listen('.user.typing', (e) => {

        if (selectedUserId == e.sender_id) {

            if (e.is_typing) {
                $('#typingStatus').text('typing...');
            } else {
                $('#typingStatus').text('');
            }

        }
    });

</script> --}}

<script>
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ env('PUSHER_APP_KEY') }}",
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        forceTLS: true
    });

    $(document).ready(function () {

        let selectedUserId = null;
        let selectedUserName = '';

        //===========================
        // USER SELECT
        //===========================
        $('#userList').on('click', 'li', function () {

            $('#userList li').removeClass('active');
            $(this).addClass('active');

            selectedUserId = $(this).data('user-id');
            selectedUserName = $(this).data('user-name');
            let selectedUserAvatar = $(this).data('user-avatar');

            $('#chatInput').prop('disabled', false);
            $('#sendMessageBtn').prop('disabled', false);

            $('#chatUserName').html(`
                <div class="d-flex align-items-center">
                    <img src="${selectedUserAvatar}"
                        class="rounded-circle mr-2"
                        width="40" height="40">
                    <span>${selectedUserName}</span>
                </div>
            `);

            $('#chatContent').html('');
            $(this).find('.badge').remove();

            // load old messages
            $.get(`/messages/${selectedUserId}`, function (messages) {
                messages.forEach(msg => {
                    appendMessage(msg.message, msg.user_id == {{ auth()->id() }} ? 'sent' : 'received');
                });
            });

            // mark as read
            $.post('/mark-as-read', {
                _token: "{{ csrf_token() }}",
                sender_id: selectedUserId
            });
        });



        //===========================
        // SEND MESSAGE
        //===========================
        $('#sendMessageBtn').click(function () {
            let text = $('#chatInput').val();
            if (text.trim() === '' || !selectedUserId) return;

            $.post('/send-message', {
                _token: "{{ csrf_token() }}",
                message: text,
                receiver_id: selectedUserId
            });

            appendMessage(text, 'sent');
            $('#chatInput').val('');
            sendTypingStatus(false);
        });



        //===========================
        // TYPING STATUS
        //===========================
        let typingTimer;

        $('#chatInput').on('input', function () {

            sendTypingStatus(true);

            clearTimeout(typingTimer);

            typingTimer = setTimeout(() => {
                sendTypingStatus(false);
            }, 1200);
        });


        $('#chatInput').keypress(function (e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#sendMessageBtn').click();
                sendTypingStatus(false);
            }
        });


        //===========================
        // SEND TYPING STATUS FUNCTION
        //===========================
        function sendTypingStatus(isTyping) {
            if (!selectedUserId) return;

            $.post('/typing-status', {
                _token: "{{ csrf_token() }}",
                receiver_id: selectedUserId,
                is_typing: isTyping
            });
        }



        //===========================
        // PRIVATE CHANNEL LISTEN
        //===========================
        const myId = {{ auth()->id() }};
        const channel = window.Echo.private('chat.' + myId);


        // listen new message
        channel.listen('.message.sent', (e) => {
            let senderId = e.user_id;

            if (selectedUserId == senderId) {
                appendMessage(e.message, 'received');

                $.post('/mark-as-read', {
                    _token: "{{ csrf_token() }}",
                    sender_id: senderId
                });

            } else {
                let badge = $(`#userList li[data-user-id="${senderId}"] .badge`);
                if (badge.length) {
                    badge.text(parseInt(badge.text()) + 1);
                } else {
                    $(`#userList li[data-user-id="${senderId}"]`)
                        .append('<span class="badge badge-danger ml-auto">1</span>');
                }
            }
        });


        // listen typing status
        channel.listen('.user.typing', (e) => {

            if (selectedUserId == e.sender_id) {
                $('#typingStatus').text(e.is_typing ? 'typing...' : '');
            }
        });


        //===========================
        // APPEND MESSAGE FUNCTION
        //===========================
        function appendMessage(text, type) {
            let msgDiv = $('<div>').addClass('chat-message ' + type);
            let msgText = $('<div>').addClass('message').text(text);
            msgDiv.append(msgText);
            $('#chatContent').append(msgDiv);
            $('#chatContent').scrollTop($('#chatContent')[0].scrollHeight);
        }

    });
</script>


</body>
</html>
