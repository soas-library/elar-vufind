function filterELARWebTrafficDepositListsPrivate() {
    
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
    
    
    checkResourcesByDeposit(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, funding_body, current_superseded);
 
    document.getElementById("statisticsFormDepositos").action="PrivateStatsDepositlist?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&funding_body="+funding_body+"&current_superseded="+current_superseded+"&source=ajax";

    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;
    
}


function checkResourcesByDeposit(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, funding_body, current_superseded) {

  var content = '';

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getResourcesByDeposit',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo, funding_body:funding_body, current_superseded:current_superseded},
    success: function(response) {
      content = "<table id='example' class='table table-striped table-bordered' cellspacing='0' width='auto'><thead><tr><th class='col-sm-2 table-align'>Node Id</th><th class='col-sm-2 table-align'>Deposit name</th><th class='col-sm-2 table-align'>Deposit Id</th><th class='col-sm-2 table-align'>Project Id</th><th class='col-sm-2 table-align'>Resources O</th><th class='col-sm-2 table-align'>Resources U</th><th class='col-sm-2 table-align'>Resources S</th><th class='col-sm-2 table-align'>Total resources</th><th class='col-sm-6 table-align'>Status</th></tr></thead><tbody>";
      if(response.status == 'OK' && response.data != null) {
        $.each(response.data, function(i, result) {
          //console.log(result.item);
          //console.log(result.firstname);
          //console.log(result.surname);
          //console.log(result.email);
          //console.log(result.user_id);
          
          content = content + "<tr><td role='row'>" + result.item +"</td>";
          content = content + "<td role='row'>" + result.title +"</td>";
          content = content + "<td role='row'>" + result.depositId +"</td>";
          content = content + "<td role='row'>" + result.projectId +"</td>";
          content = content + "<td role='row'>" + result.countO +"</td>";
          content = content + "<td role='row'>" + result.countU +"</td>";
          content = content + "<td role='row'>" + result.countS +"</td>";
          content = content + "<td role='row'>" + result.total +"</td>";
          content = content + "<td role='row'>" + result.status +"</td>";
          content = content + "</tr>";
          
        });
      }
      content = content + "</tbody></table>";
      //document.getElementsByClassName("dataTables_wrapper")[0].innerHTML = content;
      document.getElementById("example_wrapper").innerHTML = content;

      $('#example').DataTable( {
        "lengthMenu": [[5, 10, -1], [5, 10, "All"]]
    } );
    
    
      
    }
  });
}
