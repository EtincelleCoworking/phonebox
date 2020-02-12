<template>
    <div class="container" style="padding-left: 0; padding-right: 0">
        <div v-if="session.started_at === null">
            <div class="row">
                <div class="col-6">
                    <h1 style="margin-top: 30px; font-size: 45pt; font-weight: bold">Disponible</h1>
                </div>
                <div class="col-6">
                    <div class="alert alert-danger" v-if="error_msg" style="margin-top: 40px">{{error_msg}}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <p>Pour utiliser cet espace (limité à 1h par &frac12; journée), saisissez votre code personnel
                        disponible sur la page d'accueil de l'intranet ou scannez ce code :</p>

                    <div class="text-center">
                        <img :src="'/storage/'+id+'.png'" width="300" height="300" style="margin-top: -15px"/>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row text-center">
                        <div class="col-4" style="margin-bottom: 10px" v-for="i in [1, 2, 3, 4, 5, 6, 7, 8, 9]">
                            <a class="btn btn-block btn-primary btn-lg" @click="addDigit(i)">{{i}}</a>
                        </div>
                        <div class="col-4" style="margin-bottom: 10px">
                            <a class="btn btn-block btn-default btn-lg" @click="cancelLastDigit()">C</a>
                        </div>
                        <div class="col-4" style="margin-bottom: 10px">
                            <a class="btn btn-block btn-primary btn-lg" @click="addDigit(0)">0</a>
                        </div>
                        <div class="col-4" style="margin-bottom: 10px">
                            <a class="btn btn-block btn-default btn-lg" @click="clearDigits()">X</a>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit0 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit1 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit2 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit3 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit4 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                        <div class="col-2 text-center" style="color: #999999">
                            <font-awesome-icon :icon="(digit5 === null)?'circle':'dot-circle'" size="3x"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-else>
            <div class="col-12">
                <h1 class="text-center" style="margin-top: 0.3em; font-size: 5em; font-weight: bold">
                    {{session.user.name}}
                </h1>
            </div>
            <div class="col-6">
                <div class="m-b-md">
                    <img alt="image" class="img-fluid img-circle circle-border" :src="session.user.picture_url"/>
                </div>
            </div>
            <div class="col-6">
                <div class="alert alert-danger" v-if="is_overdue">
                    <font-awesome-icon icon="exclamation-triangle"/>
                    Si vous avez besoin de cet espace, le temps d'utilisation maximal de {{session.user.name}} a été
                    atteint; demandez lui avec bienveillance à pouvoir l'utiliser.
                </div>
                <div class="text-center" style="font-size: 6em; font-weight: bold"
                     :class="{'text-danger': is_overdue, 'text-warning': is_nearly_overdue}">&nbsp;{{duration}}&nbsp;
                </div>
                <a class="btn btn-block btn-primary btn-lg" @click="completeSession()">Libérer la salle</a>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        props: ['id'],
        data: function () {
            return {
                current_digit_index: 0,
                digit0: null,
                digit1: null,
                digit2: null,
                digit3: null,
                digit4: null,
                digit5: null,
                session: {
                    started_at: null,
                    user: null
                },

                loading: true,
                errored: false,

                duration: null,
                is_nearly_overdue: false,
                is_overdue: false,
                error_msg: null

            }
        },
        computed: {
            /*duration: function () {
                if (null === this.started_at) {
                    return null;
                }
                return moment.utc(this.started_at.diff(moment())).format('hh:mm:ss');
            }*/
        },
        timers: {
            updateStatus: {
                time: 1000, // 1s
                autostart: false,
                repeat: true,
                immediate: false
            }
        },
        mounted() {
            /*
            Echo.channel('phonebox')
                .listen('.BoxUsed', (e) => {
                    //this.tasks.push(e.task);
                    //this.updateTaskStatus(this.tasks[this.tasks.length - 1]);
                })
                .listen('.BoxReleased', (e) => {
                })
            ;
            */
            var _this = this;

            axios
                .get('/api/room/' + this.id)
                .then(response => {
                    if (response.data.status === 'success') {
                        _this.$set(_this, 'session', response.data.session);
                        _this.$set(_this, 'duration', '00:00');
                        _this.$timer.start('updateStatus');
                        this.updateStatus();
                    } else {
                        alert('Une erreur est survenue');
                    }
                })
                .catch(error => {
                    console.log(error);
                    this.errored = true;
                })
                .finally(() => {
                    this.loading = false;
                })
            ;
        },
        methods: {
            addDigit(kind) {
                if (this.current_digit_index < 6) {
                    //console.log('addDigit ' + kind);
                    this.$set(this, 'digit' + this.current_digit_index++, kind);
                    //this.digits[this.current_digit_index] = kind;
                    //this.current_digit_index++;
                    this.$set(this, 'error_msg', null);

                    if (this.current_digit_index === 6) {
                        // check for auth
                        var _this = this;
                        axios
                            .post('/api/room/' + this.id + '/auth', {code: ''.concat(this.digit0, this.digit1, this.digit2, this.digit3, this.digit4, this.digit5)})
                            .then(function (response) {
                                if (response.data.status === 'success') {
                                    //  console.log(response.data);
                                    //  console.log(response.data.session);
                                    _this.$set(_this, 'session', response.data.session);
                                    _this.$set(_this, 'duration', '00:00');
                                    _this.$timer.start('updateStatus');
                                }
                            })
                            .catch(error => {
                                _this.$set(_this, 'error_msg', 'Ce code n\'est pas reconnu  ('.concat(this.digit0, this.digit1, this.digit2, this.digit3, this.digit4, this.digit5, ')'));
                                //console.log(error);
                                //  this.errored = true;
                            })
                            .finally(() => {
                                this.clearDigits();
                            })
                        ;
                    }
                }
                // console.log(this.digits);
            },
            cancelLastDigit() {
                this.$set(this, 'error_msg', null);
                if (this.current_digit_index > 0) {
                    this.$set(this, 'digit' + --this.current_digit_index, null);
                }
            },
            clearDigits() {
                //this.$set(this, 'error_msg', null);
                // this.$set(this, 'current_digit_index', 0);
                this.$set(this, 'current_digit_index', 0);
                for (var i = 0; i < 6; i++) {
                    this.$set(this, 'digit' + i, null);
                }
            },
            updateStatus() {
                var duration = moment().diff(this.session.started_at, 'minutes');
                if (duration >= 60) {
                    this.$set(this, 'duration', moment.utc(moment().diff(this.session.started_at)).format('H:mm:ss'));
                } else {
                    this.$set(this, 'duration', moment.utc(moment().diff(this.session.started_at)).format('mm:ss'));
                }
                this.$set(this, 'is_overdue', duration >= 60);
                this.$set(this, 'is_nearly_overdue', duration >= 55 && duration < 60);
            },
            completeSession() {
                var _this = this;
                axios
                    .post('/api/session/' + this.session.id)
                    .then(function (response) {
                        if (response.status === 200) {
                            _this.$set(_this, 'session', {started_at: null, user: null});
                            _this.$set(_this, 'duration', null);
                            _this.$timer.stop('updateStatus');
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        this.$set(this, 'error_msg', response.statusText);
                    })
                    .finally(() => {

                    });
            }
        }
    }
</script>
