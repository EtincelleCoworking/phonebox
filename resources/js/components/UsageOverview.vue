<template>
    <FullCalendar
        defaultView="resourceTimeGridDay"
        :datesAboveResources="true"
        :header="header"
        :plugins="calendarPlugins"
        :weekends="true"
        schedulerLicenseKey="GPL-My-Project-Is-Open-Source"
        :resources="rooms"
        minTime="07:30"
        maxTime="19:00"
        :allDaySlot="false"
        :events="{
    url: '/api/usage',
    failure: function() {
      alert('there was an error while fetching events!');
    }
  }"
    />
</template>

<script>
    import FullCalendar from '@fullcalendar/vue';
    import dayGridPlugin from '@fullcalendar/daygrid';
    import resourceTimeGrid from '@fullcalendar/resource-timegrid';
    import resourceTimelinePlugin from '@fullcalendar/resource-timeline';

    export default {
        components: {
            FullCalendar
        },
        props: ['rooms'],
        data() {
            return {
                calendarPlugins: [dayGridPlugin, resourceTimelinePlugin, resourceTimeGrid],
                header: {
                    left:   'today prev,next',
                    center: 'title',
                    right:  'resourceTimeGridDay, resourceTimeGridWeek'}
            }
        },
        mounted() {
        }

    }
</script>
