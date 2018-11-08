function filterELARWebTrafficDownloadbyuserSortPrivate() {
    
    var dateFrom = document.getElementById('datepickerFrom');
    var dateTo = document.getElementById('datepickerTo');
    var surname = document.getElementById('surname').value;


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
    

    checkDownloadByUserSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, surname, 0,"id","asc");

    document.getElementById("statisticsFormDownloadbyuser").action="PrivateStatsDownloadbyuser?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&surname="+surname+"&source=ajax";
    
    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;
    
}


function checkDownloadByUserSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, surname, page,type,sort) {
  var content = '';
  var header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
  if (type=='deposit'){
    if(sort=='asc')   header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortasc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"desc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='desc')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortdesc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='both')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
  }
  if (type=='filename_id'){
    if(sort=='asc')   header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"desc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortasc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"desc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='desc')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortdesc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='both')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
  }
  if (type=='internal_node_id'){
    if(sort=='asc')   header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"desc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortasc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"desc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='desc')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortdesc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='both')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
  }
  if (type=='datetime'){
    if(sort=='asc')   header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"desc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortasc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"desc\");'>Datetime</a</th></tr></thead>";
    if(sort=='desc')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortdesc'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
    if(sort=='both')  header = "<thead><tr><th class='col-sm-2 table-align  thdeposit sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"deposit\",\"asc\");'>Deposit</a</th><th class='col-sm-2 table-align thfilename_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"filename_id\",\"asc\");'>Filename ID</a></th><th class='col-sm-2 table-align thinternal_node_id sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"internal_node_id\",\"asc\");'>Internal node Id</a</th><th class='col-sm-2 table-align  thdatetime sortboth'><a type='button' onclick='refreshUserDownloadsByUserSort(0,\"datetime\",\"asc\");'>Datetime</a</th></tr></thead>";
  }

  $.ajax({
    dataType: 'json',
    url: path + '/AJAX/JSON?method=getDownloadByUserSort',
    data: {yearFrom:yearFrom, monthFrom:monthFrom, dayFrom:dayFrom, yearTo:yearTo, monthTo:monthTo, dayTo:dayTo, surname:surname, page:page, type:type, sort:sort},
    success: function(response) {
      content = "<table id='example22' class='table table-striped table-bordered' cellspacing='0' width='auto'>"+header+"<tbody>";
      if(response.status == 'OK' && response.data != null) {
        $.each(response.data, function(i, result) {
          content = content + "<tr><td role='row'><a href='/Collection/"+ result.deposit_node_id +"'>" + result.deposit_node_id +"</a></td>";
          content = content + "<td role='row'>" + result.filename +"</td>";
          content = content + "<td role='internal_node_id'>" + result.internal_node_id +"</td>";
          content = content + "<td role='internal_node_id'>" + result.datetime +"</td>";
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
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsByUserSort("+siguiente+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-right' aria-hidden='true'></i> Next</a>";
          }
      }
      else {
          if (siguiente > total/10 && previo!='-1') {
              document.getElementById("pageDownload").style.display = "block";
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsByUserSort("+previo+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-left' aria-hidden='true'></i> Prev</a>";
          }
          else { 
              document.getElementById("pageDownload").style.display = "block";
              document.getElementById("pageDownload").innerHTML ="<a class='btn btn-default' type='button' onclick='refreshUserDownloadsByUserSort("+previo+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-left' aria-hidden='true'></i> Prev</a>"+"<a class='btn btn-default' type='button' onclick='refreshUserDownloadsByUserSort("+siguiente+",\""+type+"\",\""+sort+"\");'><i class='fa fa-angle-right' aria-hidden='true'></i> Next</a>";
          }
      }
      document.getElementById("example2_wrapper").innerHTML = content;
     //document.getElementsByClassName("dataTables_wrapper")[2].innerHTML = content;

      
      $('#example2').DataTable( {
        "lengthMenu": [[100], ["All"]]
    } );
    
    
      
    }
  });
}

function refreshUserDownloadsByUserSort(page,type,sort) {
    var dateFrom = document.getElementById('datepickerFrom');
    var dateTo = document.getElementById('datepickerTo');

    var surname = document.getElementById('surname').value;

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

    checkDownloadByUserSort(yearFrom, monthFrom, dayFrom, yearTo, monthTo, dayTo, surname, page,type,sort);

    document.getElementById("statisticsFormDownloadbyuser").action="PrivateStatsDownloadbyuser?yearFrom="+yearFrom+"&monthFrom="+monthFrom+"&dayFrom="+dayFrom+"&yearTo="+yearTo+"&monthTo="+monthTo+"&dayTo="+dayTo+"&surname="+surname+"&source=ajax";

    var contentFrom = "<i>" + yearFrom + " " + monthNames[Number(monthFrom)] + " " + dayFrom + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsFrom")[0].innerHTML = contentFrom;
    var contentTo = "<i>" + yearTo + " " + monthNames[Number(monthTo)] + " " + dayTo + "</i>";
    document.getElementsByClassName("labelPublicStatsResultsTo")[0].innerHTML = contentTo;

}

