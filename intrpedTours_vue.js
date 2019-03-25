//Make like: https://www.intrepidtravel.com/ca/last-minute

//===== LAST MINUTE TABLE =====//
Vue.component('lastminute-table', {
  data: function () {
    return {
      trips: [],
      pluginURL: intourJSVars.pluginsUrl,
    }
  },  // end data


  methods: {
    getTrips: function () {
      axios.get(this.pluginURL+'data/tour_feed.json')
      .then((response)  =>  {
        //Make it a proper JSON array if need be
        try {
          this.trips = JSON.parse('['+response.data+']');

          //Sort by date
          this.trips.sort(function(a,b) {
            var dateA = date1 = new Date(a.tourdetails.summary.startDate);
            var dateB = date1 = new Date(b.tourdetails.summary.startDate);

            if (dateA < dateB){return -1;}
            if (dateA > dateB){return 1;}
            return 0;
          });
        }
        catch(e) {
          //Only 1 response, make sure it's an array
          this.trips.push(response.data);
        }
      });
    },  // end getTrips

    formatDate: function(getDate) {
    //Formats date.  Android doesn't like ddMMyyyy format, so have to do it manually.
      /*
      monthNames = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sept.", "Oct.", "Nov.", "Dec."];
      d = new Date(getDate);
      return d.getDate() +' '+monthNames[d.getMonth()]+' '+d.getFullYear();
      */
      var day = getDate.slice(0, 2);
      var month = getDate.slice(2, 5);
      var year = getDate.slice(5, 9);

       return day+' '+month+' '+year;
    },  // end sortTrips_Date

    formatPrice: function(value) {
      //Formats price
      return '$'+value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    }
  },  // end methods


  filters: {
       // Filter definitions
       readMore(text, length, suffix) {
         return text.split(" ").splice(0,length).join(" ")+ suffix
     }
    },


  mounted: function(){
    this.getTrips();
  },


  template: '#template-lastminute-table',

})        // end component


//===== DETAILS PAGE =====//
Vue.component('lastminute-moreinfo', {
  data: function () {
    return {
      pluginURL: intourJSVars.pluginsUrl,
    }
  },  // end data

  props: ['seltrip'],


  methods: {

  },  // end methods


  template: '#template-lastminute-moreinfo',

})        // end component


//===== INTOUR APP =====//
var app = new Vue({
  el: '#intourApp',
  data: {
    showList: true,
    showDetail: false,
    selTrip: null,
  },


  methods:{
    getSelected: function(getSelTrip){
      //What happens when a trip is selected.
      this.selTrip = getSelTrip;

      //Show the details
      this.displayDetails();

      var strTripInfo = this.selTrip.tour.summary.productName+' ('+this.selTrip.tourdetails.summary.departureCode+' - '+this.selTrip.tourdetails.summary.startDate+')';
       //Set trip info in form field
       fieldID = document.getElementById('tripinfo');
       fieldID.value = strTripInfo;
    }, // end getSelected


    displayList: function(){
    //Shows the list, hides the details
      this.showDetail = false;
      this.showList = true;

      //Scroll to selected element.  (Doesn't seem to work, needs fixing)
      var code = this.selTrip.tour.summary.productCode;
      var elem = document.getElementById(code);
      elem.scrollIntoView(true);
    },


    displayDetails: function(){
    //Shows the details, hides the list
      this.showDetail = true;
      this.showList = false;

      //DEBUG //
      //let testDate = new Date.parse();
      var d = new Date('09MAR2019');

       console.log(d.getFullYear());

      //Scroll to top
      document.getElementById('intourApp').scrollIntoView(true);
    },


    calltoAction: function(trip){
    //What happens if the client wants to book
      //('Under Development:  What happens if the client wants to book');
    },  // end calltoAction


  },  // end methods


});
