import express from 'express';
import http from 'http';
import { Server } from 'socket.io';


const app = express();

const server = http.createServer(app);


const io = new Server(server, {
    cors: {
        origin: "*",  
        methods: ["GET", "POST"]
    }
});


io.on('connection', (socket) => {
    console.log('new client connected');

    socket.on('sendChatToServer', (message) => {
        console.log("get message: ", message);
        if (message) {
           
            socket.broadcast.emit('sendChatToClient', message);
        }
    });


    socket.on('disconnect', () => {
        console.log('cliend disconnected');
    });
});


server.listen(3000, () => {
    console.log('Socket.IO server is running, port: 3000');
});
