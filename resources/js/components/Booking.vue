<template>
    <div>
        <div v-if="loading">Chargement en cours...</div>
        <div v-else>
            <b-tabs content-class="m-3">
                <b-tab :title="location" :active="tab_index === 0"
                       v-for="(ressources, location, tab_index) in this.ressources_by_location" :key="tab_index">
                    <b-form-checkbox-group v-model="selected_ressources">
                        <b-form-checkbox :value="ressource.id"
                                         @change="triggerUpdateStatusForRessource(ressource.id)"
                                         v-for="ressource in ressources" :key="ressource.id">
                            {{ressource.name}}
                            <small>
                                ({{ressource.hourly_pricing}}€/h)
                            </small>
                        </b-form-checkbox>
                    </b-form-checkbox-group>
                </b-tab>
            </b-tabs>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th width="30%" colspan="2">Date</th>
                    <th v-for="selection in selected_ressources"
                        :width="(70 / selected_ressources.length).toFixed(2) + '%'">{{getRessourceName(selection)}}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(date, date_index) in dates">
                    <td width="1%">
                        <b-button @click="deleteDate(date_index)" size="sm" variant="danger" class="float-right">X
                        </b-button>
                    </td>
                    <td>
                        <div class="row">
                            <b-form-input type="date" @change="triggerUpdateStatusForDate(date)"
                                          size="sm"
                                          class="col-6" v-model="date.day"></b-form-input>
                            <b-form-select :options="hours" @change="triggerUpdateStatusForDate(date)"
                                           size="sm"
                                           class="col-3" v-model="date.from"></b-form-select>
                            <b-form-select :options="hours" @change="triggerUpdateStatusForDate(date)"
                                           size="sm"
                                           class="col-3" v-model="date.to"></b-form-select>

                        </div>

                        <small>{{getDayName(date.day)}}</small>

                        <div v-if="date_index === dates.length-1">
                            <b-button @click="addNextDate(date, 1)" size="sm">J+1</b-button>
                            <b-button @click="addNextDate(date, 7)" size="sm">J+7</b-button>
                        </div>
                    </td>
                    <th v-for="selection in selected_ressources"
                        :class="{'table-success': (date.status[selection] === 'available'), 'table-warning': (date.status[selection] === 'confirmation_pending'), 'table-danger': (date.status[selection] === 'booked')} ">
                        <!--
                        {{date.status[selection]}}
                        -->

                        <span v-if="date.loading[selection]"><i>updating...</i></span>
                        <template v-else>
                            <b-form-checkbox v-if="is_root || (date.status[selection] === 'available')"
                                             :value="{ressource_id: selection, day: date.day, from: date.from, to: date.to}"
                                             v-model="bookings">
                            </b-form-checkbox>
                        </template>

                        <!--
                        <b-button @click="updateStatus(date, selection)" size="sm" v-else>Update</b-button>
                        -->
                    </th>
                </tr>
                </tbody>
            </table>
            <b-button @click="addNextDate">Ajouter</b-button>
            <pre>
            {{bookings}}
            </pre>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['city'],
        data: function () {
            return {
                ressources_by_location: null,
                selected_ressources: [],
                dates: [],
                loading: true,
                errored: false,
                hours: [],
                bookings: [],
                is_root: true // allow booking even if ressource is not available
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
            /*   updateStatus: {
                   time: 1000, // 1s
                   autostart: false,
                   repeat: true,
                   immediate: false
               }*/
        },
        mounted() {
            for (var h = 7; h <= 23; h++) {
                for (var m = 0; m < 60; m += 15) {
                    var value = '';
                    if (h < 10) {
                        value = '0';
                    }
                    value = value + h + ':';
                    if (m < 10) {
                        value = value + '0';
                    }
                    value = value + m;
                    this.hours.push({'value': value, 'text': value});
                }
            }

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
                .get('https://intranet.coworking-toulouse.com/api/1.0/city/' + this.city + '/ressources')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.ressources_by_location = response.data.data;
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

            this.addNextDate();
        },
        methods: {
            getRessourceName(id) {
                for (var location in this.ressources_by_location) {
                    if (this.ressources_by_location.hasOwnProperty(location)) {
                        var ressources = this.ressources_by_location[location];
                        for (var index in ressources) {
                            if (ressources.hasOwnProperty(index)) {
                                if (ressources[index].id === id) {
                                    return ressources[index].name;
                                }
                            }
                        }
                    }
                }
                return '-';
            },
            getDayName(day) {
                return moment(day).format('dddd');
            },
            addNextDate(date, diff) {
                if (typeof date === 'undefined') {
                    date = null;
                }
                var d = date ? moment(date.day) : moment();
                if (typeof diff !== 'undefined') {
                    d.add(diff, 'day');
                }
                var new_date = {
                    day: d.format('YYYY-MM-DD'),
                    from: date ? date.from : '08:30',
                    to: date ? date.to : '18:00',
                    status: {},
                    loading: {}
                };
                this.dates.push(new_date);
                for (var index in this.selected_ressources) {
                    if (this.selected_ressources.hasOwnProperty(index)) {
                        this.updateStatus(new_date, this.selected_ressources[index]);
                    }
                }
            },
            updateStatus(date, ressouce_id) {
                var _this = this;
                var _date = date;
                var _ressouce_id = ressouce_id;

                this.$set(date.loading, ressouce_id, true);
                axios
                    .get('https://intranet.coworking-toulouse.com/api/1.0/city/' + this.city + '/ressource/' + ressouce_id + '/status',
                        {
                            params: {
                                from: date.day + ' ' + date.from
                                , to: date.day + ' ' + date.to
                            }
                        }
                    )
                    .then(response => {
                        if (response.data.status === 'success') {
                            _this.$set(_date.status, _ressouce_id, response.data.data.status);
                        } else {
                            alert('Une erreur est survenue');
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        //this.errored = true;
                    })
                    .finally(() => {
                        _this.$set(_date.loading, ressouce_id, false);
                    })
                ;
            },
            triggerUpdateStatusForRessource(ressource_id) {
                // item is not yet added to the selected_ressources when this event is triggered
                if (!this.selected_ressources.includes(ressource_id)) {
                    for (var index in this.dates) {
                        if (this.dates.hasOwnProperty(index)) {
                            this.updateStatus(this.dates[index], ressource_id);
                        }
                    }
                } else {
                    // delete items
                    var i = this.bookings.length - 1;
                    while (i >= 0) {
                        if (this.bookings[i].ressource_id === ressource_id) {
                            this.bookings.splice(i, 1);
                        }
                        i--;
                    }
                }
            },
            triggerUpdateStatusForDate(date) {
                for (var index in this.selected_ressources) {
                    if (this.selected_ressources.hasOwnProperty(index)) {
                        this.updateStatus(date, this.selected_ressources[index]);
                    }
                }
            },
            deleteDate(index) {
                var date = this.dates[index];
                var i = this.bookings.length - 1;
                while (i >= 0) {
                    if ((this.bookings[i].day === date.day)
                        && (this.bookings[i].from === date.from)
                        && (this.bookings[i].to === date.to)
                    ) {
                        this.bookings.splice(i, 1);
                    }
                    i--;
                }
                this.dates.splice(index, 1);
            }

        }
    }
</script>
