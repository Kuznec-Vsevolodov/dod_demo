<template>
    <div class="col-md-8 chat-content-block">
        <div class="chat-block">
            <div class="message-list" id="messages_list" ref="messagesList">
                <!-- Вывод сообщений -->
                    <div class="message-row" v-for="message in messages" v-if="messages != []">
                        <div class="outside_message" v-if="message.author != user.name">
                            <!-- условия для вывода первого сообщения в группе -->
                            <div class="main_message-out message_out" v-if="
                                    messages[indexChecker(message)-1].author != message.author">
                                <img :src="'/images/avatars/'+message.author_avatar" alt="" class="avatar-image">
                                <div class="message-struct">
                                    <div class="message-header" v-if="
                                            messages[indexChecker(message)-1].author != message.author
                                        ">
                                        <h6 class="acc-name">{{message.author}}</h6>
                                        <p class="time">{{message.time}}</p>
                                    </div>
                                    <div class="message-body">
                                        <p>{{message.text}}</p>
                                        <img class="message_img" v-if="message.image != false" :src="'/images/chats/'+message.image">
                                    </div>
                                </div>
                            </div>
                            <!-- условия для вывода второго сообщения в группе -->
                            <div class="message_out inside_out_message" v-else-if="messages[indexInChecker(message)+1].author == message.author">
                                <div class="message-struct">
                                    <div class="message-body">
                                        <p>{{message.text}}</p>
                                        <img class="message_img" v-if="message.image != false" :src="'/images/chats/'+message.image">
                                    </div>
                                </div>
                            </div>
                            <!-- условия для вывода последнего сообщения в группе -->
                            <div class="message_out end_out_message" v-else>
                                <div class="message-struct">
                                    <div class="message-body">
                                        <p>{{message.text}}</p>
                                        <img class="message_img" v-if="message.image != false" :src="'/images/chats/'+message.image">
                                    </div>
                                </div>
                            </div>
                        </div>    
                        <!-- условия для вывода сообщения автора -->
                        <div class="message-in-row" v-else>
                            <div class="message-in">
                                <p>{{message.text}}</p>  
                                <img class="message_img" v-if="message.image != false" :src="'/images/chats/'+message.image">  
                            </div> 
                            <p class="time time-in">{{message.time}}</p>
                        </div>
                    </div>     
            </div>
            <div class="input-data-block">
                <div class="main-input">
                    <input type="text" v-model="dataInput" placeholder="Сообщение..." class="message-input" ref="MessageEnterInput" @keyup.enter="sendMessage">
                    <input type="file" ref="imgInputData" id="image-out" class="image-input" v-on:change="imgUpload()">
                    <label for="image-out" class="image-label"><img src="/images/paper-clip.svg"></label>
                    <button type="submit" @click="sendMessage"><img src="/images/sender.svg" alt=""></button>
                </div>
            </div>
        </div>  
    </div>
</template>

<script>
    export default {
        props: ['chat_channel_id', 'user'],
        data(){
            return{
                dataInput: '',
                messages: [],
                conditionVisibility: false,
                chat_type: '',
                imgInputData: ''
            }
        },
        mounted() {
            this.$refs.MessageEnterInput.focus(),
            this.socketConnect(),
            this.getMessages() 
        },
        methods: {
            // подключение к сокету
            socketConnect(){
                console.log(this.user.game_in)
                // соединение с комнатой
                socket.emit('joinRoom', {username: this.user.name, room: this.chat_channel_id, avatar: this.user.avatar, game_type: this.user.game_in});
                // обработчик сообщений
                socket.on('message', message => {
                    this.messages.push(message);
                    setTimeout(() => {  this.$refs.messagesList.scrollTop = this.$refs.messagesList.scrollHeight; }, 150);
                    
                })
            },
            // загрузка изображений
            imgUpload(){
                this.imgInputData = this.$refs.imgInputData.files[0];
                console.log(this.imgInputData);
            },
            // отправка сообщений
            sendMessage(){
                
                // формирование сообщения в зависимости от наличия изображения
                var image = false;
                if(this.imgInputData !== ''){
                    // с изображением
                    let form = new FormData();
                    form.append('image', this.imgInputData);
                    axios.post('http://127.0.0.1:8000/chat/image-load', form)
                    .then((response) => { 
                        this.imgInputData = '';
                        image = response.data
                        console.log(image)
                        socket.emit('chatMessage', {'msg': this.dataInput, 'image': response.data});
                        this.dataInput = '';
                        this.$refs.MessageEnterInput.focus()
                    });    
                }else{
                    // без изображения
                    socket.emit('chatMessage', {'msg': this.dataInput, 'image': false});
                    this.dataInput = '';
                    this.$refs.MessageEnterInput.focus()
                }
            },
            // получение сообщений с сервера
            getMessages(){
                axios.post('http://127.0.0.1:3000/getchat-'+this.user.game_in, {
                    chat_id: this.chat_channel_id
                }).then((response) => {
                    console.log(response.data)
                    this.messages = response.data;
                })
            },
            // определитель позиции сообщений
            indexChecker(item){
                if(this.messages.indexOf(item) == 0){
                    return 1;
                }else{
                    return this.messages.indexOf(item);
                }
            },
            // определитель позиции сообщений внутри группы
            indexInChecker(item){
                if(this.messages.indexOf(item) == 0){
                    return -1;
                }else if(this.messages.indexOf(item) == this.messages.length-1){
                    return this.messages.indexOf(item)-1
                }else{
                    return this.messages.indexOf(item)
                } 
            }
        }
    }
</script>

<style scoped>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,300&display=swap');

    .chat-block{
        background: #272727;
        height: 740px;
        width: 100%;
        border-radius: 15px;
        padding-top: 31px;
        padding-bottom: 20px;
        padding-left: 23px;
        padding-right: 5px;
    }

    .message-list{
        overflow: auto;
        height: 81%;
    }

    .message-list::-webkit-scrollbar-thumb{
        border-radius: 50px;
        background-color: #6A8ED4;
    }
    .message-list::-webkit-scrollbar {
        -webkit-appearance: none;
        background-color: #676767;
        width: 7px;
        border-radius: 50px;
        padding-top: 5px !important;
    }
    .message-row{
        width: 100%;
    }
    .chat-users-list{
        display: flex;
        justify-content: center;
        color: #fff;
        font-size: 14px;
    }
    .message_out{
        background: #6A8ED4;
        width: max-content;
        padding-top: 10px;
        padding-bottom: 14px;
        padding-left: 14px;
        padding-right: 23px;
        display: flex; 
        box-sizing: border-box;
        margin-bottom: 5px;
    }
    .main_message-out{
        border-radius: 0px 15px 15px 15px;
        margin-top: 15px;
    }
    .inside_out_message{
        border-radius: 15px;
    }
    .end_out_message{
        border-radius: 15px 15px 15px 0px;
    }
    .avatar-image{
        display: block;
        width: 48px;
        height: 48px;
        border-radius: 50px;
    }
    .message-struct{
        display: flex;
        flex-direction: column;
        margin-left: 9px;
        width: 100%;
    }
    .message-header{
        width: 100%;
        display: flex;
        justify-content: space-between;
    }
    .acc-name{
        font-size: 16px;
        line-height: 19px;
        color: #fff;
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
        margin: 0;
    }
    .time{
        font-size: 14px;
        margin: 0;
        color: #FFFFFF;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
        margin-left: 5px;
    }
    .message-body{
        margin-top: 7px;
    }
    .message-body p{
        max-width: 323px;
        margin: 0;
        color: #fff;
        font-size: 14px;
        line-height: 16px;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
    }
    .message_img{
        max-width: 400px;
        border-radius: 15px;
        margin-top: 15px;
    }
    .message-in-row{
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding-right: 10px;
    }

    .message-in{
        background: #676767;
        border-radius: 15px 0px 15px 15px;
        padding-right: 18px;
        padding-left: 24px;
        padding-bottom: 20px;
        padding-top: 18px;
        width: max-content;
    }
    .message-in p{
        max-width: 323px;
        margin: 0px;
        color: #fff;
        font-size: 14px;
        line-height: 16px;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
    }
    .time-in{
        color: #676767;
    }
    .input-data-block{
        padding-right: 17px;
    }
    .main-input{
        width: 100%;
        padding-top: 20px;
        padding-bottom: 27px;
        display: flex;
        align-items: center;
    }
    .image-input{
        display: none;
    }
    .image-label{
        width: 47px;
        height: 47px;
        background: #698DD2;
        border: none;
        border-radius: 50px;
        margin-left: 15px;
        margin-bottom: 0px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .image-label img{
        width: 22px;
        height: 22px;
    }
    .message-input{
        width: 78%;
        height: 47px;
        background: #4F4F4F;
        border: none;
        border-radius: 50px;
        padding-left: 30px;
        color: #fff;
        font-size: 14px;
        line-height: 16px;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
    }
    .main-input button{
        width: 47px;
        height: 47px;
        background: #698DD2;
        border: none;
        border-radius: 50px;
        margin-left: 15px;
    }
    .def-buttons{
        width: 100%;
        display: flex;
        justify-content: space-between;
        padding-right: 15px;
    }
    .def-buttons button{
        width: 46%;
        color: #fff;
        font-size: 14px;
        line-height: 16px;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
        height: 28px;
        border: none;
        border-radius: 50px;
        background-color:  #4F4F4F;
    }
    .service_accidense {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .service_accidense button{
        margin-top: 17px;
        color: #fff;
        font-size: 14px;
        line-height: 16px;
        font-weight: 400;
        font-family: 'Roboto', sans-serif;
        height: 28px;
        border: none;
        border-radius: 50px;
        background-color:  #282828;
    }
</style>