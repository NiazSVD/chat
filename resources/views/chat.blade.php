<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ChatApp</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
        }

        .chat-box {
            max-width: 500px;
            margin: 20px auto;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            height: 70vh;
            display: flex;
            flex-direction: column;
        }

        .chat-content {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
            padding-right: 10px;
        }

        .message {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .my-message {
            justify-content: flex-start;
        }

        .my-message p {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .server-message {
            justify-content: flex-end;
        }

        .server-message p {
            background-color: #e1e1e1;
            color: black;
            padding: 10px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-input-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .chat-input {
            border-radius: 30px;
            padding: 10px;
            width: 80%;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        .send-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
        }

        .send-btn:disabled {
            background-color: #c5c5c5;
        }

        .message-time {
            font-size: 0.8rem;
            color: gray;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="chat-box">
        <!-- Chat Content -->
        <div class="chat-content" id="chatContent">
            <ul id="chatList">
                
            </ul>
        </div>

        <!-- Chat Input Section -->
        <div class="chat-input-section">
            <input type="text" id="messageInput" class="chat-input" placeholder="Type your message..." />
            <button class="send-btn" id="sendBtn" disabled>&#8594;</button>
        </div>
    </div>
</div>

<!-- jQuery and Socket.IO -->
<script src="https://code.jquery.com/jquery-3.6.0.js" crossorigin="anonymous"></script>
<script src="https://cdn.socket.io/4.0.1/socket.io.min.js" crossorigin="anonymous"></script>

<script>
    $(function() {
        let ip_address = '127.0.0.1';  
        let socket_port = '3000';
        let socket = io(ip_address + ':' + socket_port);

        let messageInput = $('#messageInput');
        let sendBtn = $('#sendBtn');

        
        messageInput.on('input', function() {
            let message = $(this).val().trim();
            sendBtn.prop('disabled', message.length === 0);  
        });

        // Send button click
        sendBtn.click(function() {
            let message = messageInput.val().trim();
            if (message) {
                socket.emit('sendChatToServer', message);  
                displayMessage(message, 'my-message');  
                messageInput.val('');  
                sendBtn.prop('disabled', true); 
            }
        });

        // Enter key press
        messageInput.keypress(function(e) {
            if (e.which === 13 && !e.shiftKey) {
                sendBtn.click();  
                return false;
            }
        });

       
        socket.on('sendChatToClient', function(message) {
            displayMessage(message, 'server-message'); 
        });

       
        function displayMessage(message, className) {
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            $('#chatList').append(`
                <li class="message ${className}">
                    <p>${message}</p><span class="message-time">${time}</span>
                </li>
            `);
            $('.chat-content').scrollTop($('.chat-content')[0].scrollHeight);  
        }
    });
</script>

</body>
</html>
