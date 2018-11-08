function filterELARWebTrafficDownloadbyfiletypePrivate() {
    
    var dateFrom = document.getElementById('datepickerFrom');
    var dateTo = document.getElementById('datepickerTo');

    var selectedFB = new Array();
    $("input:checkbox[name=fundingbody]:checked").each(function(){
        selectedFB.push($(this).val());
    });

    var selectedCS = new Array();
    $("input:checkbox[name=current_or_superseded]:checked").each(function(){
        selectedCS.push($(this).val());
    });

    var funding_body = selectedFB;
    var current_superseded = selectedCS;
    
    var dF = dateFrom.value.split("/");
    var dT = dateTo.value.split("/");
    
    var dayFrom = dF[0];
    var monthFrom = dF[1];
    var yearFrom = dF[2];
    var dayTo = dT[0];
    var monthTo = dT[1];
    var yearTo = dT[2];
    

    
    var monthNames = ["","January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
    ];
    

    
    checkDownloadFilesByFileType(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo,funding_body, current_superseded);
 
    document.getElementById("statisticsFormFilesByFileType").action="PrivateStatsDownloadbyfiletype?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&funding_body="+funding_body+"&current_superseded="+current_superseded+"&source=ajax";
    
    
    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;
    
}


function checkDownloadFilesByFileType (yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, funding_body, current_superseded) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getDownloadFilesByFileType',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo, funding_body:funding_body, current_superseded:current_superseded},
    success: function(response) {
      content = "<table id='example' class='table table-striped table-bordered' cellspacing='0' width='auto'><thead><tr><th class='col-sm-2 table-align'>File Type</th><th class='col-sm-2 table-align'>Number</th><th class='col-sm-2 table-align'>Size</th><th class='col-sm-2 table-align'>Duration</th><th class='col-sm-2 table-align'>O</th><th class='col-sm-2 table-align'>O%</th><th class='col-sm-2 table-align'>U</th><th class='col-sm-2 table-align'>U%</th><th class='col-sm-2 table-align'>S</th><th class='col-sm-2 table-align'>S%</th></tr></thead><tbody>";
      if(response.status == 'OK' && response.data != null) {
          kk=response.data;
          kk=kk.slice(0,-1);
        $.each(kk, function(i, result) {
          //console.log(result.name);
          //console.log(result.firstname);
          //console.log(result.surname);
          //console.log(result.email);
          //console.log(result.user_id);
          
          content = content + "<tr><td role='row'>" + result.file_type +"</td>";
          content = content + "<td role='row'>" + result.count +"</td>";
          content = content + "<td role='row'>" + result.size +"</td>";

          content = content + "<td role='row'>" + result.duration +"</td>";
          content = content + "<td role='row'>" + result.countO +"</td>";
          content = content + "<td role='row'>" + result.percentO +"</td>";
          content = content + "<td role='row'>" + result.countU +"</td>";
          content = content + "<td role='row'>" + result.percentU +"</td>";
          content = content + "<td role='row'>" + result.countS +"</td>";
          content = content + "<td role='row'>" + result.percentS +"</td>";
          content = content + "</tr>";
          total = result.count;
          
	  if(i+1 == '10000')
	  {
		limit = '1';
	  }

        });
      }
      content = content + "</tbody>";


      content2 = "<tfoot>";
      if(response.status == 'OK' && response.data != null) {
          kk=response.data;
          kk=kk.splice(-1,1);
        $.each(kk, function(i, result) {
          //console.log(result.name);
          //console.log(result.firstname);
          //console.log(result.surname);
          //console.log(result.email);
          //console.log(result.user_id);
          
          content2 = content2 + "<tr><td role='row'>" + result.file_type +"</td>";
          content2 = content2 + "<td role='row'>" + result.count +"</td>";
          content2 = content2 + "<td role='row'>" + result.size +"</td>";

          content2 = content2 + "<td role='row'>" + result.duration +"</td>";
          content2 = content2 + "<td role='row'>" + result.countO +"</td>";
          content2 = content2 + "<td role='row'>" + result.percentO +"</td>";
          content2 = content2 + "<td role='row'>" + result.countU +"</td>";
          content2 = content2 + "<td role='row'>" + result.percentU +"</td>";
          content2 = content2 + "<td role='row'>" + result.countS +"</td>";
          content2 = content2 + "<td role='row'>" + result.percentS +"</td>";
          content2 = content2 + "</tr>";
          total = result.count;
          
	  if(i+1 == '10000')
	  {
		limit = '1';
	  }

        });
      }
      content2 = content2 + "</tfoot></table>";



      //document.getElementsByClassName("dataTables_wrapper")[0].innerHTML = content;
      document.getElementById("example3_wrapper").innerHTML = content + content2;

      $('#example').DataTable( {
        "lengthMenu": [[5, 10, -1], [5, 10, "All"]]
    } );
    
    
      
    }
  });
}
