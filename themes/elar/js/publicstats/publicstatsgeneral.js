function filterELARWebTrafficPublic() {
    
    var dateFrom = document.getElementById('datepickerFrom');
    var dateTo = document.getElementById('datepickerTo');
    
    //console.log(dateFrom.value);
    //console.log(dateTo.value);
    
    var dF = dateFrom.value.split("/");
    var dT = dateTo.value.split("/");
    
    var dayFrom = dF[0];
    var monthFrom = dF[1];
    var yearFrom = dF[2];
    var dayTo = dT[0];
    var monthTo = dT[1];
    var yearTo = dT[2];
    
    //console.log(dayFrom);
    //console.log(monthFrom);
    //console.log(yearFrom);
    //console.log(dayTo);
    //console.log(monthTo);
    //console.log(yearTo);
    
    var monthNames = ["","January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
    ];
    
    //var y = document.getElementById('years');
    //var m = document.getElementById('months');
    
    //var year = y.value;
    //var month = m.value;
    
    //console.log(year);
    //console.log(month);
    
    checkPageHitsResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkUserLoginsResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkOAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkUAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkSAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkClicsVisitResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkDurationOfVisitResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkCountryPercentage(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkOCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkUCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkSCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    //checkResourcesByDeposit(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    //checkResourcesUserDownload(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    
    //document.getElementById("statisticsForm").action="PrivateStats?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&source=ajax";
    //document.getElementById("statisticsFormDepositos").action="PrivateStats?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&source=ajax";
    //document.getElementById("statisticsFormUserDownload").action="PrivateStats?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&source=ajax";
    
    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;
    
}


function checkPageHitsResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getPageHitsResults',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo },
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.hits != null)
          	content = result.hits;
          else
          	content = '0';
        });
      }
      
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("pageHitsResults")[0].innerHTML = content;
      
    }
  });
}

function checkUserLoginsResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getUserLoginsResults',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.hits != null)
          	content = result.hits;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("userLoginsResults")[0].innerHTML = content;
      
    }
  });
}

function checkOAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getOAccessResults',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("OAccessResults")[0].innerHTML = content;
      
    }
  });
}

function checkUAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getUAccessResults',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("UAccessResults")[0].innerHTML = content;
      
    }
  });
}

function checkSAccessResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getSAccessResults',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("SAccessResults")[0].innerHTML = content;
      
    }
  });
}

function checkClicsVisitResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getClicsPerVisit',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("clicsPerVisitResults")[0].innerHTML = content;
      
    }
  });
}

function checkCountryPercentage(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getCountryPercentage',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = '{' + result.num + '}';
          else
          	content = '0';
        });
      }
      
      var newData = JSON.parse(content.replace(/array\(/g, '{').replace(/\)/g, '}').replace(/=>/g, ':'));
      
      jQuery.noConflict();
    	jQuery(function(){
    	var $ = jQuery;
    	
    	/*var newData = {"US": 10000,"BE":20000,"CA": 28000,"CN":  31000,"DE":  16000};*/
    	
	$( '#map' ).contents().remove();
	
	$('#map').vectorMap({
        map: 'world_mill_en',
        panOnDrag: true,
        focusOn: {
          x: 0.5,
          y: 0.5,
          scale: 2,
          animate: true
        },
		
        series: {
          regions: [{
            scale: ['#C8EEFF', '#0071A4'],
            normalizeFunction: 'polynomial',
            values: newData
          }]
        },
        onRegionTipShow: function(e, el, code){
        
          if (typeof(newData[code])=='undefined')
           {
           	el.html(el.html() + '<p>Count: 0</p>');
           } else {
           	el.html(el.html() + '<p>Count: ' + newData[code] + '</p>');
           }
        }
		
      });
      
    });
      
    }
    
  });
}


function checkDurationOfVisitResults(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getDurationOfVisit',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("clicsDurationPerVisit")[0].innerHTML = content;
      
    }
  });
}

function checkOCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getOCount',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("Oresources")[0].innerHTML = content;
      
    }
  });
}


function checkSCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getSCount',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("Sresources")[0].innerHTML = content;
      
    }
  });
}

function checkUCount(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getUCount',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
          if(result.num != null)
          	content = result.num;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      document.getElementsByClassName("Uresources")[0].innerHTML = content;
      
    }
  });
}



