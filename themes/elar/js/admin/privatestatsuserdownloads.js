function filterELARWebTrafficUserdownloadSortPrivate() {
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
    

    
    var monthNames = ["","January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
    ];
    

    checkResourcesUserDownloadSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo,0, "user", "both");
 
    document.getElementById("statisticsFormUserDownload").action="PrivateStatsUserdownloads?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&source=ajax";
    
    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;
    
}


function checkResourcesUserDownloadSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, page, type, sort) {
  var content = '';
  total=0;
  header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";

  if (type=='user'){
    if(sort=='asc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortasc' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"desc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
    if(sort=='desc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortdesc' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
    if(sort=='both') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
  }
  if (type=='time'){
    if(sort=='asc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"desc\");'>User</a></th><th class='col-sm-2 table-align thtime sortasc' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"desc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
    if(sort=='desc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortdesc' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
    if(sort=='both') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
  }
  if (type=='resource_path'){
    if(sort=='asc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"desc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortasc' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"desc\");'>Resource path</a></th></tr></thead>";
    if(sort=='desc') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortdesc' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
    if(sort=='both') header = "<thead><tr><th class='col-sm-2 table-align thuser sortboth' id='thuser'><a type='button' onclick='refreshUserDownloadsSort(0,\"user\",\"asc\");'>User</a></th><th class='col-sm-2 table-align thtime sortboth' id='thtime'><a type='button' onclick='refreshUserDownloadsSort(0,\"time\",\"asc\");'>Time</a></th><th class='col-sm-2 table-align thresource_path sortboth' id='thresource_path'><a type='button' onclick='refreshUserDownloadsSort(0,\"resource_path\",\"asc\");'>Resource path</a></th></tr></thead>";
  }

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getUserDownloadSort',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo, page:page, type:type, sort:sort},
    success: function(response) {

      content = "<table id='example22' class='table table-striped table-bordered' cellspacing='0' width='auto'>"+header+"</thead><tbody>";

      if(response.status == 'OK' && response.data != null) {
        $.each(response.data, function(i, result) {
          //console.log(result.name);
          //console.log(result.firstname);
          //console.log(result.surname);
          //console.log(result.email);
          //console.log(result.user_id);
          
          content = content + "<tr><td role='row'>" + result.name +"</td>";
          content = content + "<td role='row'>" + result.datetime +"</td>";
          content = content + "<td role='row'>" + result.path +"</td>";
          content = content + "</tr>";
          total = result.count;
          
        });
      }
      content = content + "</tbody></table>";

      document.getElementById("pageDownload").style.display = "none";
      previo = parseInt(page)-1;
      previo =  previo+"";
      siguiente = parseInt(page)+1;
      siguiente = siguiente+"";
      total = parseInt(total);
      if (previo=='-1') {
          if (total>10) {
              document.getElementById("pageDownload").style.display = "block";
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsSort("+siguiente+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-right' aria-hidden='true'></i> Next</a>";
          }
      }
      else {
          if (siguiente > total/10 && previo!='-1') {
              document.getElementById("pageDownload").style.display = "block";
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsSort("+previo+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-left' aria-hidden='true'></i> Prev</a>";
          }
          else { 
              document.getElementById("pageDownload").style.display = "block";
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsSort("+previo+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-left' aria-hidden='true'></i> Prev</a>"+"<a class='btn btn-default' type='button' onclick='refreshUserDownloadsSort("+siguiente+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-right' aria-hidden='true'></i> Next</a>";
          }
      }
   document.getElementById("example2_wrapper").innerHTML = content;

      $('#example2').DataTable( {
        "lengthMenu": [[100], ["All"]]
    } );
    
    
      
    }
  });
}


function refreshUserDownloadsSort(page, type, sort) {
    var dateFrom = document.getElementById('datepickerFrom');
    var dateTo = document.getElementById('datepickerTo');

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

    checkResourcesUserDownloadSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, page, type, sort);
    

    document.getElementById("statisticsFormUserDownload").action="PrivateStats?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&source=ajax";

    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;

}


