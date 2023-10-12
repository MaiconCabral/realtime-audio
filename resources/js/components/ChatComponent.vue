<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"> Pet Chat</div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-3">
                                <ul class="nav flex-column">
                                     <li class="nav-item" v-for="user in userItens" :key="user.id">
                                        <a class="nav-link" :class="{'bg-info': received.indexOf(user.id) > -1}" @click="loadMessages(user)">{{ user.name }}</a>
                                    </li>
                                 
                                </ul>
                            </div>

                            <div class="col-md-9" v-if="!started">
                                <div class="panel bg-primary-subtle p-2 text-dark" v-if="!loading">
                                    <div class="panel-heading">
                                        Falando com <strong>{{ currentChat.name }}</strong>
                                    </div>
                                </div>
                                <div class="panel bg-warning p-2 text-dark bg-opacity-25" v-if="messages.length === 0">
                                    <div class="panel-heading">
                                        Nenhuma  mensagem para ouvir
                                    </div>
                                </div>
                                <div class="panel" :class="{'bg-warning p-2 text-dark bg-opacity-10': currentChat.id == message.sender}" v-for="message in messages">
                                    <div class="panel-body">
                                        <audio controls>
                                            <source :src="'/audios/' + message.audio">
                                        </audio>
                                    </div>
                                </div>

                                <a id="rec" :class="{'recording': recording}" @click="rec()"></a>
                            </div>

                            <div class="col-md-9" v-if="started">
                                <div class="panel bg-success p-2 text-dark bg-opacity-10" v-if="!loading">
                                    <div class="panel-heading">
                                        Escolha alguém para conversar...
                                    </div>
                                </div>
                                <div class="panel bg-info p-2 text-dark bg-opacity-10" v-if="loading">
                                    <div class="panel-heading">
                                        Obtendo conversa...
                                    </div>
                                </div>
                                <div class="panel bg-danger p-2 text-dark bg-opacity-10" v-if="error">
                                    <div class="panel-heading">
                                        Erro ao carregar a conversa...
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name:"chat-list",
        data(){
          return{
            started: true,
            loading: false,
            error: false,
            currentChat: {},
            userItens:[],
            messages: [],
            recording: false,
            recorder: null,
            recordedData: [],
            received: []
            }
        },
        created(){
            this.axios.get('/users').then((response) => {
                this.userItens = response.data;
             });
        },
        methods: {
            loadMessages(user){
                // console.log(user)

                this.loading = true;
                this.started = true;
                
                let index = this.received.indexOf(user.id);

                if (index > -1) {
                    this.received.splice(index, 1);
                }

                this.axios.get('/messages/' + user.id)
                    .then((response) => {
                        this.currentChat = user;
                        this.loading = false;
                        this.started = false;
                        this.messages = response.data;
                    })
                    .catch(() => {
                        this.loading = false;
                        this.error = true;
                    })

                
            },
            rec(){
                this.recording = !this.recording;

                if(this.recording){
                    this.startRec();
                }else{
                    this.stopRec();
                }
            },
            startRec(){
                console.log('start');
                const config = {
                    audio: true,
                    video: false
                };

                const successCallback = (stream) => {
                    this.recorder = new MediaRecorder(stream);
        
                    this.recorder.ondataavailable = (e) => {
                        this.recordedData.push(e.data);
                        console.log(this.recordedData);
                    }

                    this.recorder.onstop = () => {
                        // console.log('parou de gravar');

                        let blob = new Blob(this.recordedData, {type: 'video/webm'});
                        
                        this.recorder = null;
                        this.recordedData = [];

                        let formData = new FormData();
                        formData.append('audio', blob);
                        formData.append('receiver', this.currentChat.id);

                        window.axios.post('/messages', formData).then((response) => {
                            // console.log('done');
                            this.messages.splice(0, 0, response.data);
                        })

                    }

                    this.recorder.start();
                }

                const errorCallback = (err) => {
                    console.log(err);
                }

                 navigator.getUserMedia(config, successCallback, errorCallback);
               //navigator.mediaDevices.getUserMedia(config, successCallback, errorCallback);
            },
            stopRec(){
                // console.log('stop');
                this.recorder.stop();
            }

        },
        mounted(){
            //  this.fetchData()
            window.Echo.channel('channel.messages.')
                .listen('AudioSended', (e) => {
                    // console.log('ouvindo');
                    if (e.message.sender === this.currentChat.id) {
                        this.messages.splice(0, 0, e.message);
                    } else {
                        this.received.push(e.message.sender);
                    }
                });
                
        },
    }
</script>
<style>
@keyframes pulse {
    50% {
        background: transparent;
    }
}

a {
    cursor: pointer;
}

#rec {
    position: fixed;
    right: 10px;
    bottom: 10px;
}
#rec:after {
    display: block;
    content: '';
    width: 30px;
    height: 30px;
    background: red;
    border-radius: 30px;
}
#rec.recording:after {
    animation: pulse 1s infinite;
}

.nav-link:hover, .nav-link:focus {
    color: #333;
    background: #ededed;
    border-radius: 5px;
}

</style>