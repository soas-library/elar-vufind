function checkDepositHits(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getDepositHits',
    data: {node_id:node_id, yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK') {
        $.each(response.data, function(i, result) {
        	//console.log(result.hits);
          if(result.hits != null)
          	content = result.hits;
          else
          	content = '0';
        });
      }
      // Solo hay uno, creamos el div de nuevo
      var x = document.getElementsByClassName("hitsResults")[0].innerHTML = content;
      
    }
  });
}

function showHideO() {
	if ($("#O").is(":visible")) {
		$("#O").hide();
	} else {
		$("#O").show();
	}
}

function showHideU() {
	if ($("#U").is(":visible")) {
		$("#U").hide();
	} else {
		$("#U").show();
	}
}

function showHideS() {
	if ($("#S").is(":visible")) {
		$("#S").hide();
	} else {
		$("#S").show();
	}
}

function checkFilesDownloaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1) {

  var content = '';
  var prev_res_type = '';
  var total_files_down = 0;
  var tot_res_type_o = 0;
  var tot_res_type_u = 0;
  var tot_res_type_s = 0;

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getFilesDownloaded',
    data: {node_id:node_id, yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK' && response.data != null) {
        content = "<span><b>Downloaded files</b></span><div class='files_stat_text'>";
        $.each(response.data, function(i, result) {
          //console.log(result.resource_type);
          //console.log(result.file_type);
          //console.log(result.count_files);
          
          total_files_down = total_files_down + parseInt(result.count_files);
          
          if(result.resource_type == 'O') {
          	tot_res_type_o = tot_res_type_o + parseInt(result.count_files);
          } else if (result.resource_type == 'U') {
          	tot_res_type_u = tot_res_type_u + parseInt(result.count_files);
          } else if (result.resource_type == 'S') {
          	tot_res_type_s = tot_res_type_s + parseInt(result.count_files);
          }
          if(prev_res_type != result.resource_type) {
          	if(prev_res_type != '')
          		content = content + "</div>";
          	prev_res_type = result.resource_type;
          	content = content + "<div id='type" + result.resource_type + "' class='depositorMargin'><span class='m-urcs'><span class='vd'>" + result.resource_type + "</span></span><span class='total_" + result.resource_type + "'>" + result.count_files + "</span><span class='data'><a onclick='showHide" + result.resource_type + "();'><i class='fa fa-caret-down' aria-hidden='true'></i></a></span></div>";
          	content = content + "<div id='" + result.resource_type + "' style='display: none;'>";
          }
          content = content + "<div class='depositorMargin'><span>" + result.file_type + "</span><span class='data'>" + result.count_files + "</span></div>";
          
        });
      } else {
        // display the error message on each of the ajax status place holder
        content = content + "<span><b>Files downloaded:</b></span><div class='files_stat_text'><div class='depositorMargin'><span>" + mess1 + "</span></div></div>";
      }
      content = content + "</div></div>";
      
      /*var scriptContent = '<script class="ejemplo">$("#typeO").click(function(){if ($("#O").is(":visible")) {console.log("Hide O 3");$("#O").hide();} else {console.log("Show O 3");$("#O").show();}});';
      scriptContent = scriptContent + '$("#typeU").click(function(){if ($("#U").is(":visible")) {console.log("Hide U 3");$("#U").hide();} else {console.log("Show U 3");$("#U").show();}});';
      scriptContent = scriptContent + '$("#typeS").click(function(){if ($("#S").is(":visible")) {console.log("Hide S 3");$("#S").hide();} else {console.log("Show S 3");$("#S").show();}});</script>';
      
      content = content + scriptContent;*/
      
      if(total_files_down != 0)
      	content = content + "<div class='depositorMargin'><span><b>Total:</b></span><span class='data'>" + total_files_down + "</span></div>";
      // Solo hay uno, creamos el div de nuevo
      var x = document.getElementsByClassName("fileResults")[0].innerHTML = content;
      
      if(tot_res_type_o != 0)
      	var x = document.getElementsByClassName("total_O")[0].innerHTML = tot_res_type_o;
      if(tot_res_type_u != 0)
      	var x = document.getElementsByClassName("total_U")[0].innerHTML = tot_res_type_u;
      if(tot_res_type_s != 0)
      	var x = document.getElementsByClassName("total_S")[0].innerHTML = tot_res_type_s;
      
    }
  });
}

function checkUsersDownloaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1) {

  var content = '';
  var total_user_down = 0;

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getUsersDownloaded',
    data: {node_id:node_id, yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK' && response.data != null) {
        content = "<span><b>Active Users</b></span><div class='files_stat_text'>";
        $.each(response.data, function(i, result) {
          //console.log(result.num);
          //console.log(result.firstname);
          //console.log(result.surname);
          //console.log(result.email);
          //console.log(result.user_id);
          
          total_user_down = total_user_down + parseInt(result.num);
          
          content = content + "<div class='depositorMargin'><span><a href='/Statistics/Home?node_id=" + node_id + "&user=" + result.user_id + "&yearFrom=" + yearFrom + "&monthFrom=" + monthFrom + "&dayFrom=" + dayFrom + "&yearTo=" + yearTo + "&monthTo=" + monthTo + "&dayTo=" + dayTo + "' target='_blank' style='text-decoration: underline;'>" + result.firstname + " " + result.surname + "</a></span><span class='data'>" + result.num +"</span></div>";
          
        });
      } else {
        // display the error message on each of the ajax status place holder
        content = content + "<span><b>Active Users</b></span><div class='files_stat_text'><div class='depositorMargin'><span>" + mess1 + "</span></div></div>";
      }
      content = content + "</div>";
      if(total_user_down != 0)
      	content = content + "<div class='depositorMargin'><span><b>Total downloads:</b></span><span class='data'><a href='/Statistics/Home?node_id=" + node_id + "&yearFrom=" + yearFrom + "&monthFrom=" + monthFrom + "&dayFrom=" + dayFrom + "&yearTo=" + yearTo + "&monthTo=" + monthTo + "&dayTo=" + dayTo + "' target='_blank' style='text-decoration: underline;'>" + total_user_down + "</a></span></div>";
      // Solo hay uno, creamos el div de nuevo
      var x = document.getElementsByClassName("userResults")[0].innerHTML = content;
      
    }
  });
}

function checkFilesUploaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1) {

  var content = '';
  var total_user_uplo = 0;

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getFilesUploaded',
    data: {node_id:node_id, yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo},
    success: function(response) {
      if(response.status == 'OK' && response.data != null) {
        content = "<span><b>Uploaded files</b></span><div class='files_stat_text'>";
        $.each(response.data, function(i, result) {
          //console.log(result.num);
          //console.log(result.file_type);
          
          total_user_uplo = total_user_uplo + parseInt(result.num);
          
          content = content + "<div class='depositorMargin'><span>" + result.file_type + "</a></span><span class='data'>" + result.num +"</span></div>";
          
        });
      } else {
        // display the error message on each of the ajax status place holder
        content = content + "<span><b>Uploaded files</b></span><div class='files_stat_text'><div class='depositorMargin'><span>" + mess1 + "</span></div></div>";
      }
      content = content + "</div>";
      if(total_user_uplo != 0)
      	content = content + "<div class='depositorMargin'><span><b>Total uploads:</b></span><span class='data'><a href='/UploadedFiles/Home?node_id=" + node_id + "&yearFrom=" + yearFrom + "&monthFrom=" + monthFrom + "&dayFrom=" + dayFrom + "&yearTo=" + yearTo + "&monthTo=" + monthTo + "&dayTo=" + dayTo + "' target='_blank' style='text-decoration: underline;'>" + total_user_uplo + "</a></span></div>";
      // Solo hay uno, creamos el div de nuevo
      var x = document.getElementsByClassName("filesUploaded")[0].innerHTML = content;
      
    }
  });
}

function filterFilesDownloaded(node_id, mess1) {
    
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
    
    checkDepositHits(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo);
    checkFilesDownloaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1);
    if ($(".userResults").is(":visible")) {
    	checkUsersDownloaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1);
    }
    if ($(".filesUploaded").is(":visible")) {
    	checkFilesUploaded(node_id, yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, mess1);
    }
    
    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelResultsTo")[0].innerHTML = contentTo;

}











