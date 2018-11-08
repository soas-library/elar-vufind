/*global checkSaveStatuses, deparam, extractClassParams, htmlEncode, Lightbox, path, syn_get_widget, vufindString */

/**
 * Functions and event handlers specific to record pages.
 */
function checkRequestIsValid(element, requestURL, requestType, blockedClass) {
  var recordId = requestURL.match(/\/Record\/([^\/]+)\//)[1];
  var vars = {}, hash;
  var hashes = requestURL.slice(requestURL.indexOf('?') + 1).split('&');

  for(var i = 0; i < hashes.length; i++)
  {
    hash = hashes[i].split('=');
    var x = hash[0];
    var y = hash[1];
    vars[x] = y;
  }
  vars['id'] = recordId;

  var url = path + '/AJAX/JSON?' + $.param({method:'checkRequestIsValid', id: recordId, requestType: requestType, data: vars});
  $.ajax({
    dataType: 'json',
    cache: false,
    url: url,
    success: function(response) {
      if (response.status == 'OK') {
        if (response.data.status) {
          $(element).removeClass('disabled')
            .attr('title', response.data.msg)
            .html('<i class="fa fa-flag"></i>&nbsp;'+response.data.msg);
        } else {
          $(element).remove();
        }
      } else if (response.status == 'NEED_AUTH') {
        $(element).replaceWith('<span class="' + blockedClass + '">' + response.data.msg + '</span>');
      }
    }
  });
}

function setUpCheckRequest() {
  $('.checkRequest').each(function(i) {
    if ($(this).hasClass('checkRequest')) {
      var isValid = checkRequestIsValid(this, this.href, 'Hold', 'holdBlocked');
    }
  });
  $('.checkStorageRetrievalRequest').each(function(i) {
    if ($(this).hasClass('checkStorageRetrievalRequest')) {
      var isValid = checkRequestIsValid(this, this.href, 'StorageRetrievalRequest',
          'StorageRetrievalRequestBlocked');
    }
  });
  $('.checkILLRequest').each(function(i) {
    if ($(this).hasClass('checkILLRequest')) {
      var isValid = checkRequestIsValid(this, this.href, 'ILLRequest',
          'ILLRequestBlocked');
    }
  });
}

function deleteRecordComment(element, recordId, recordSource, commentId) {
  var url = path + '/AJAX/JSON?' + $.param({method:'deleteRecordComment',id:commentId});
  $.ajax({
    dataType: 'json',
    url: url,
    success: function(response) {
      if (response.status == 'OK') {
        $($(element).parents('.comment')[0]).remove();
      }
    }
  });
}

function refreshCommentList(recordId, recordSource) {
  var url = path + '/AJAX/JSON?' + $.param({method:'getRecordCommentsAsHTML',id:recordId,'source':recordSource});
  $.ajax({
    dataType: 'json',
    url: url,
    success: function(response) {
      // Update HTML
      if (response.status == 'OK') {
        $('#commentList').empty();
        $('#commentList').append(response.data);
        $('input[type="submit"]').button('reset');
        $('.delete').unbind('click').click(function() {
          var commentId = $(this).attr('id').substr('recordComment'.length);
          deleteRecordComment(this, recordId, recordSource, commentId);
          return false;
        });
      }
    }
  });
}

function registerAjaxCommentRecord() {
  // Form submission
  $('form[name="commentRecord"]').unbind('submit').submit(function(){
    var form = this;
    var id = form.id.value;
    var recordSource = form.source.value;
    var url = path + '/AJAX/JSON?' + $.param({method:'commentRecord'});
    var data = {
      comment:form.comment.value,
      id:id,
      source:recordSource
    };
    $.ajax({
      type: 'POST',
      url:  url,
      data: data,
      dataType: 'json',
      success: function(response) {
        var form = 'form[name="commentRecord"]';
        if (response.status == 'OK') {
          refreshCommentList(id, recordSource);
          $(form).find('textarea[name="comment"]').val('');
          $(form).find('input[type="submit"]').button('loading');
        } else {
          Lightbox.displayError(response.data);
        }
      }
    });
    return false;
  });
  // Delete links
  $('.delete').click(function(){deleteRecordComment(this, $('.hiddenId').val(), $('.hiddenSource').val(), this.id.substr(13));return false;});
}

function registerTabEvents() {

  // register the record comment form to be submitted via AJAX
  registerAjaxCommentRecord();

  setUpCheckRequest();

  // Place a Hold
  // Place a Storage Hold
  // Place an ILL Request
  $('.placehold,.placeStorageRetrievalRequest,.placeILLRequest').click(function() {
    var parts = $(this).attr('href').split('?');
    parts = parts[0].split('/');
    var params = deparam($(this).attr('href'));
    params.id = parts[parts.length-2];
    params.hashKey = params.hashKey.split('#')[0]; // Remove #tabnav
    return Lightbox.get('Record', parts[parts.length-1], params, false, function(html) {
      Lightbox.checkForError(html, Lightbox.changeContent);
    });
  });
}

function ajaxLoadTab(tabid) {
  var id = $('.hiddenId')[0].value;
  // Try to parse out the controller portion of the URL. If this fails, or if
  // we're flagged to skip AJAX for this tab, just return true and let the
  // browser handle it.
  var urlroot = document.URL.match(new RegExp('/[^/]+/'+id));
  if(!urlroot || document.getElementById(tabid).parentNode.className.indexOf('noajax') > -1) {
    return true;
  }
  $.ajax({
    url: path + urlroot + '/AjaxTab',
    type: 'POST',
    data: {tab: tabid},
    success: function(data) {
      $('#record-tabs .tab-pane.active').removeClass('active');
      $('#'+tabid+'-tab').html(data).addClass('active');
      $('#'+tabid).tab('show');
      registerTabEvents();
      if(typeof syn_get_widget === "function") {
        syn_get_widget();
      }
    }
  });
  return false;
}

$(document).ready(function(){


  var id = $('.hiddenId')[0].value;
  //SCB New params
  //var nodeid = $('.nodeid')[0].value;
  //var filename = $('.filename')[0].value;
  //var userid = $('.userid')[0].value;

  //SCB New params
//if ( $('.nodeid')[0] ) { var nodeid = $('.nodeid')[0].value;   var filename = $('.filename')[0].value;      var userid = $('.userid')[0].value;}
if ( $('.nodeid')[0] ) { var nodeid = $('.nodeid')[0].value;   var filename = $('.filename')[0].value;      var userid = $('.userid')[0].value;}
if ( $('.nodeid')[1] ) { var nodeid001 = $('.nodeid')[1].value;	  var filename001 = $('.filename')[1].value;	  var userid001 = $('.userid')[1].value;}
if ( $('.nodeid')[2] ) { var nodeid002 = $('.nodeid')[2].value;	  var filename002 = $('.filename')[2].value;	  var userid002 = $('.userid')[2].value;}
if ( $('.nodeid')[3] ) { var nodeid003 = $('.nodeid')[3].value;	  var filename003 = $('.filename')[3].value;	  var userid003 = $('.userid')[3].value;}
if ( $('.nodeid')[4] ) { var nodeid004 = $('.nodeid')[4].value;	  var filename004 = $('.filename')[4].value;	  var userid004 = $('.userid')[4].value;}
if ( $('.nodeid')[5] ) { var nodeid005 = $('.nodeid')[5].value;	  var filename005 = $('.filename')[5].value;	  var userid005 = $('.userid')[5].value;}
if ( $('.nodeid')[6] ) { var nodeid006 = $('.nodeid')[6].value;	  var filename006 = $('.filename')[6].value;	  var userid006 = $('.userid')[6].value;}
if ( $('.nodeid')[7] ) { var nodeid007 = $('.nodeid')[7].value;	  var filename007 = $('.filename')[7].value;	  var userid007 = $('.userid')[7].value;}
if ( $('.nodeid')[8] ) { var nodeid008 = $('.nodeid')[8].value;	  var filename008 = $('.filename')[8].value;	  var userid008 = $('.userid')[8].value;}
if ( $('.nodeid')[9] ) { var nodeid009 = $('.nodeid')[9].value;	  var filename009 = $('.filename')[9].value;	  var userid009 = $('.userid')[9].value;}
if ( $('.nodeid')[10] ) { var nodeid010 = $('.nodeid')[10].value;	  var filename010 = $('.filename')[10].value;	  var userid010 = $('.userid')[10].value;}
if ( $('.nodeid')[11] ) { var nodeid011 = $('.nodeid')[11].value;	  var filename011 = $('.filename')[11].value;	  var userid011 = $('.userid')[11].value;}
if ( $('.nodeid')[12] ) { var nodeid012 = $('.nodeid')[12].value;	  var filename012 = $('.filename')[12].value;	  var userid012 = $('.userid')[12].value;}
if ( $('.nodeid')[13] ) { var nodeid013 = $('.nodeid')[13].value;	  var filename013 = $('.filename')[13].value;	  var userid013 = $('.userid')[13].value;}
if ( $('.nodeid')[14] ) { var nodeid014 = $('.nodeid')[14].value;	  var filename014 = $('.filename')[14].value;	  var userid014 = $('.userid')[14].value;}
if ( $('.nodeid')[15] ) { var nodeid015 = $('.nodeid')[15].value;	  var filename015 = $('.filename')[15].value;	  var userid015 = $('.userid')[15].value;}
if ( $('.nodeid')[16] ) { var nodeid016 = $('.nodeid')[16].value;	  var filename016 = $('.filename')[16].value;	  var userid016 = $('.userid')[16].value;}
if ( $('.nodeid')[17] ) { var nodeid017 = $('.nodeid')[17].value;	  var filename017 = $('.filename')[17].value;	  var userid017 = $('.userid')[17].value;}
if ( $('.nodeid')[18] ) { var nodeid018 = $('.nodeid')[18].value;	  var filename018 = $('.filename')[18].value;	  var userid018 = $('.userid')[18].value;}
if ( $('.nodeid')[19] ) { var nodeid019 = $('.nodeid')[19].value;	  var filename019 = $('.filename')[19].value;	  var userid019 = $('.userid')[19].value;}
if ( $('.nodeid')[20] ) { var nodeid020 = $('.nodeid')[20].value;	  var filename020 = $('.filename')[20].value;	  var userid020 = $('.userid')[20].value;}
if ( $('.nodeid')[21] ) { var nodeid021 = $('.nodeid')[21].value;	  var filename021 = $('.filename')[21].value;	  var userid021 = $('.userid')[21].value;}
if ( $('.nodeid')[22] ) { var nodeid022 = $('.nodeid')[22].value;	  var filename022 = $('.filename')[22].value;	  var userid022 = $('.userid')[22].value;}
if ( $('.nodeid')[23] ) { var nodeid023 = $('.nodeid')[23].value;	  var filename023 = $('.filename')[23].value;	  var userid023 = $('.userid')[23].value;}
if ( $('.nodeid')[24] ) { var nodeid024 = $('.nodeid')[24].value;	  var filename024 = $('.filename')[24].value;	  var userid024 = $('.userid')[24].value;}
if ( $('.nodeid')[25] ) { var nodeid025 = $('.nodeid')[25].value;	  var filename025 = $('.filename')[25].value;	  var userid025 = $('.userid')[25].value;}
if ( $('.nodeid')[26] ) { var nodeid026 = $('.nodeid')[26].value;	  var filename026 = $('.filename')[26].value;	  var userid026 = $('.userid')[26].value;}
if ( $('.nodeid')[27] ) { var nodeid027 = $('.nodeid')[27].value;	  var filename027 = $('.filename')[27].value;	  var userid027 = $('.userid')[27].value;}
if ( $('.nodeid')[28] ) { var nodeid028 = $('.nodeid')[28].value;	  var filename028 = $('.filename')[28].value;	  var userid028 = $('.userid')[28].value;}
if ( $('.nodeid')[29] ) { var nodeid029 = $('.nodeid')[29].value;	  var filename029 = $('.filename')[29].value;	  var userid029 = $('.userid')[29].value;}
if ( $('.nodeid')[30] ) { var nodeid030 = $('.nodeid')[30].value;	  var filename030 = $('.filename')[30].value;	  var userid030 = $('.userid')[30].value;}
if ( $('.nodeid')[31] ) { var nodeid031 = $('.nodeid')[31].value;	  var filename031 = $('.filename')[31].value;	  var userid031 = $('.userid')[31].value;}
if ( $('.nodeid')[32] ) { var nodeid032 = $('.nodeid')[32].value;	  var filename032 = $('.filename')[32].value;	  var userid032 = $('.userid')[32].value;}
if ( $('.nodeid')[33] ) { var nodeid033 = $('.nodeid')[33].value;	  var filename033 = $('.filename')[33].value;	  var userid033 = $('.userid')[33].value;}
if ( $('.nodeid')[34] ) { var nodeid034 = $('.nodeid')[34].value;	  var filename034 = $('.filename')[34].value;	  var userid034 = $('.userid')[34].value;}
if ( $('.nodeid')[35] ) { var nodeid035 = $('.nodeid')[35].value;	  var filename035 = $('.filename')[35].value;	  var userid035 = $('.userid')[35].value;}
if ( $('.nodeid')[36] ) { var nodeid036 = $('.nodeid')[36].value;	  var filename036 = $('.filename')[36].value;	  var userid036 = $('.userid')[36].value;}
if ( $('.nodeid')[37] ) { var nodeid037 = $('.nodeid')[37].value;	  var filename037 = $('.filename')[37].value;	  var userid037 = $('.userid')[37].value;}
if ( $('.nodeid')[38] ) { var nodeid038 = $('.nodeid')[38].value;	  var filename038 = $('.filename')[38].value;	  var userid038 = $('.userid')[38].value;}
if ( $('.nodeid')[39] ) { var nodeid039 = $('.nodeid')[39].value;	  var filename039 = $('.filename')[39].value;	  var userid039 = $('.userid')[39].value;}
if ( $('.nodeid')[40] ) { var nodeid040 = $('.nodeid')[40].value;	  var filename040 = $('.filename')[40].value;	  var userid040 = $('.userid')[40].value;}
if ( $('.nodeid')[41] ) { var nodeid041 = $('.nodeid')[41].value;	  var filename041 = $('.filename')[41].value;	  var userid041 = $('.userid')[41].value;}
if ( $('.nodeid')[42] ) { var nodeid042 = $('.nodeid')[42].value;	  var filename042 = $('.filename')[42].value;	  var userid042 = $('.userid')[42].value;}
if ( $('.nodeid')[43] ) { var nodeid043 = $('.nodeid')[43].value;	  var filename043 = $('.filename')[43].value;	  var userid043 = $('.userid')[43].value;}
if ( $('.nodeid')[44] ) { var nodeid044 = $('.nodeid')[44].value;	  var filename044 = $('.filename')[44].value;	  var userid044 = $('.userid')[44].value;}
if ( $('.nodeid')[45] ) { var nodeid045 = $('.nodeid')[45].value;	  var filename045 = $('.filename')[45].value;	  var userid045 = $('.userid')[45].value;}
if ( $('.nodeid')[46] ) { var nodeid046 = $('.nodeid')[46].value;	  var filename046 = $('.filename')[46].value;	  var userid046 = $('.userid')[46].value;}
if ( $('.nodeid')[47] ) { var nodeid047 = $('.nodeid')[47].value;	  var filename047 = $('.filename')[47].value;	  var userid047 = $('.userid')[47].value;}
if ( $('.nodeid')[48] ) { var nodeid048 = $('.nodeid')[48].value;	  var filename048 = $('.filename')[48].value;	  var userid048 = $('.userid')[48].value;}
if ( $('.nodeid')[49] ) { var nodeid049 = $('.nodeid')[49].value;	  var filename049 = $('.filename')[49].value;	  var userid049 = $('.userid')[49].value;}
if ( $('.nodeid')[50] ) { var nodeid050 = $('.nodeid')[50].value;	  var filename050 = $('.filename')[50].value;	  var userid050 = $('.userid')[50].value;}
if ( $('.nodeid')[51] ) { var nodeid051 = $('.nodeid')[51].value;	  var filename051 = $('.filename')[51].value;	  var userid051 = $('.userid')[51].value;}
if ( $('.nodeid')[52] ) { var nodeid052 = $('.nodeid')[52].value;	  var filename052 = $('.filename')[52].value;	  var userid052 = $('.userid')[52].value;}
if ( $('.nodeid')[53] ) { var nodeid053 = $('.nodeid')[53].value;	  var filename053 = $('.filename')[53].value;	  var userid053 = $('.userid')[53].value;}
if ( $('.nodeid')[54] ) { var nodeid054 = $('.nodeid')[54].value;	  var filename054 = $('.filename')[54].value;	  var userid054 = $('.userid')[54].value;}
if ( $('.nodeid')[55] ) { var nodeid055 = $('.nodeid')[55].value;	  var filename055 = $('.filename')[55].value;	  var userid055 = $('.userid')[55].value;}
if ( $('.nodeid')[56] ) { var nodeid056 = $('.nodeid')[56].value;	  var filename056 = $('.filename')[56].value;	  var userid056 = $('.userid')[56].value;}
if ( $('.nodeid')[57] ) { var nodeid057 = $('.nodeid')[57].value;	  var filename057 = $('.filename')[57].value;	  var userid057 = $('.userid')[57].value;}
if ( $('.nodeid')[58] ) { var nodeid058 = $('.nodeid')[58].value;	  var filename058 = $('.filename')[58].value;	  var userid058 = $('.userid')[58].value;}
if ( $('.nodeid')[59] ) { var nodeid059 = $('.nodeid')[59].value;	  var filename059 = $('.filename')[59].value;	  var userid059 = $('.userid')[59].value;}
if ( $('.nodeid')[60] ) { var nodeid060 = $('.nodeid')[60].value;	  var filename060 = $('.filename')[60].value;	  var userid060 = $('.userid')[60].value;}
if ( $('.nodeid')[61] ) { var nodeid061 = $('.nodeid')[61].value;	  var filename061 = $('.filename')[61].value;	  var userid061 = $('.userid')[61].value;}
if ( $('.nodeid')[62] ) { var nodeid062 = $('.nodeid')[62].value;	  var filename062 = $('.filename')[62].value;	  var userid062 = $('.userid')[62].value;}
if ( $('.nodeid')[63] ) { var nodeid063 = $('.nodeid')[63].value;	  var filename063 = $('.filename')[63].value;	  var userid063 = $('.userid')[63].value;}
if ( $('.nodeid')[64] ) { var nodeid064 = $('.nodeid')[64].value;	  var filename064 = $('.filename')[64].value;	  var userid064 = $('.userid')[64].value;}
if ( $('.nodeid')[65] ) { var nodeid065 = $('.nodeid')[65].value;	  var filename065 = $('.filename')[65].value;	  var userid065 = $('.userid')[65].value;}
if ( $('.nodeid')[66] ) { var nodeid066 = $('.nodeid')[66].value;	  var filename066 = $('.filename')[66].value;	  var userid066 = $('.userid')[66].value;}
if ( $('.nodeid')[67] ) { var nodeid067 = $('.nodeid')[67].value;	  var filename067 = $('.filename')[67].value;	  var userid067 = $('.userid')[67].value;}
if ( $('.nodeid')[68] ) { var nodeid068 = $('.nodeid')[68].value;	  var filename068 = $('.filename')[68].value;	  var userid068 = $('.userid')[68].value;}
if ( $('.nodeid')[69] ) { var nodeid069 = $('.nodeid')[69].value;	  var filename069 = $('.filename')[69].value;	  var userid069 = $('.userid')[69].value;}
if ( $('.nodeid')[70] ) { var nodeid070 = $('.nodeid')[70].value;	  var filename070 = $('.filename')[70].value;	  var userid070 = $('.userid')[70].value;}
if ( $('.nodeid')[71] ) { var nodeid071 = $('.nodeid')[71].value;	  var filename071 = $('.filename')[71].value;	  var userid071 = $('.userid')[71].value;}
if ( $('.nodeid')[72] ) { var nodeid072 = $('.nodeid')[72].value;	  var filename072 = $('.filename')[72].value;	  var userid072 = $('.userid')[72].value;}
if ( $('.nodeid')[73] ) { var nodeid073 = $('.nodeid')[73].value;	  var filename073 = $('.filename')[73].value;	  var userid073 = $('.userid')[73].value;}
if ( $('.nodeid')[74] ) { var nodeid074 = $('.nodeid')[74].value;	  var filename074 = $('.filename')[74].value;	  var userid074 = $('.userid')[74].value;}
if ( $('.nodeid')[75] ) { var nodeid075 = $('.nodeid')[75].value;	  var filename075 = $('.filename')[75].value;	  var userid075 = $('.userid')[75].value;}
if ( $('.nodeid')[76] ) { var nodeid076 = $('.nodeid')[76].value;	  var filename076 = $('.filename')[76].value;	  var userid076 = $('.userid')[76].value;}
if ( $('.nodeid')[77] ) { var nodeid077 = $('.nodeid')[77].value;	  var filename077 = $('.filename')[77].value;	  var userid077 = $('.userid')[77].value;}
if ( $('.nodeid')[78] ) { var nodeid078 = $('.nodeid')[78].value;	  var filename078 = $('.filename')[78].value;	  var userid078 = $('.userid')[78].value;}
if ( $('.nodeid')[79] ) { var nodeid079 = $('.nodeid')[79].value;	  var filename079 = $('.filename')[79].value;	  var userid079 = $('.userid')[79].value;}
if ( $('.nodeid')[80] ) { var nodeid080 = $('.nodeid')[80].value;	  var filename080 = $('.filename')[80].value;	  var userid080 = $('.userid')[80].value;}
if ( $('.nodeid')[81] ) { var nodeid081 = $('.nodeid')[81].value;	  var filename081 = $('.filename')[81].value;	  var userid081 = $('.userid')[81].value;}
if ( $('.nodeid')[82] ) { var nodeid082 = $('.nodeid')[82].value;	  var filename082 = $('.filename')[82].value;	  var userid082 = $('.userid')[82].value;}
if ( $('.nodeid')[83] ) { var nodeid083 = $('.nodeid')[83].value;	  var filename083 = $('.filename')[83].value;	  var userid083 = $('.userid')[83].value;}
if ( $('.nodeid')[84] ) { var nodeid084 = $('.nodeid')[84].value;	  var filename084 = $('.filename')[84].value;	  var userid084 = $('.userid')[84].value;}
if ( $('.nodeid')[85] ) { var nodeid085 = $('.nodeid')[85].value;	  var filename085 = $('.filename')[85].value;	  var userid085 = $('.userid')[85].value;}
if ( $('.nodeid')[86] ) { var nodeid086 = $('.nodeid')[86].value;	  var filename086 = $('.filename')[86].value;	  var userid086 = $('.userid')[86].value;}
if ( $('.nodeid')[87] ) { var nodeid087 = $('.nodeid')[87].value;	  var filename087 = $('.filename')[87].value;	  var userid087 = $('.userid')[87].value;}
if ( $('.nodeid')[88] ) { var nodeid088 = $('.nodeid')[88].value;	  var filename088 = $('.filename')[88].value;	  var userid088 = $('.userid')[88].value;}
if ( $('.nodeid')[89] ) { var nodeid089 = $('.nodeid')[89].value;	  var filename089 = $('.filename')[89].value;	  var userid089 = $('.userid')[89].value;}
if ( $('.nodeid')[90] ) { var nodeid090 = $('.nodeid')[90].value;	  var filename090 = $('.filename')[90].value;	  var userid090 = $('.userid')[90].value;}
if ( $('.nodeid')[91] ) { var nodeid091 = $('.nodeid')[91].value;	  var filename091 = $('.filename')[91].value;	  var userid091 = $('.userid')[91].value;}
if ( $('.nodeid')[92] ) { var nodeid092 = $('.nodeid')[92].value;	  var filename092 = $('.filename')[92].value;	  var userid092 = $('.userid')[92].value;}
if ( $('.nodeid')[93] ) { var nodeid093 = $('.nodeid')[93].value;	  var filename093 = $('.filename')[93].value;	  var userid093 = $('.userid')[93].value;}
if ( $('.nodeid')[94] ) { var nodeid094 = $('.nodeid')[94].value;	  var filename094 = $('.filename')[94].value;	  var userid094 = $('.userid')[94].value;}
if ( $('.nodeid')[95] ) { var nodeid095 = $('.nodeid')[95].value;	  var filename095 = $('.filename')[95].value;	  var userid095 = $('.userid')[95].value;}
if ( $('.nodeid')[96] ) { var nodeid096 = $('.nodeid')[96].value;	  var filename096 = $('.filename')[96].value;	  var userid096 = $('.userid')[96].value;}
if ( $('.nodeid')[97] ) { var nodeid097 = $('.nodeid')[97].value;	  var filename097 = $('.filename')[97].value;	  var userid097 = $('.userid')[97].value;}
if ( $('.nodeid')[98] ) { var nodeid098 = $('.nodeid')[98].value;	  var filename098 = $('.filename')[98].value;	  var userid098 = $('.userid')[98].value;}
if ( $('.nodeid')[99] ) { var nodeid099 = $('.nodeid')[99].value;	  var filename099 = $('.filename')[99].value;	  var userid099 = $('.userid')[99].value;}
if ( $('.nodeid')[100] ) { var nodeid100 = $('.nodeid')[100].value;	  var filename100 = $('.filename')[100].value;	  var userid100 = $('.userid')[100].value;}
if ( $('.nodeid')[101] ) { var nodeid101 = $('.nodeid')[101].value;	  var filename101 = $('.filename')[101].value;	  var userid101 = $('.userid')[101].value;}
if ( $('.nodeid')[102] ) { var nodeid102 = $('.nodeid')[102].value;	  var filename102 = $('.filename')[102].value;	  var userid102 = $('.userid')[102].value;}
if ( $('.nodeid')[103] ) { var nodeid103 = $('.nodeid')[103].value;	  var filename103 = $('.filename')[103].value;	  var userid103 = $('.userid')[103].value;}
if ( $('.nodeid')[104] ) { var nodeid104 = $('.nodeid')[104].value;	  var filename104 = $('.filename')[104].value;	  var userid104 = $('.userid')[104].value;}
if ( $('.nodeid')[105] ) { var nodeid105 = $('.nodeid')[105].value;	  var filename105 = $('.filename')[105].value;	  var userid105 = $('.userid')[105].value;}
if ( $('.nodeid')[106] ) { var nodeid106 = $('.nodeid')[106].value;	  var filename106 = $('.filename')[106].value;	  var userid106 = $('.userid')[106].value;}
if ( $('.nodeid')[107] ) { var nodeid107 = $('.nodeid')[107].value;	  var filename107 = $('.filename')[107].value;	  var userid107 = $('.userid')[107].value;}
if ( $('.nodeid')[108] ) { var nodeid108 = $('.nodeid')[108].value;	  var filename108 = $('.filename')[108].value;	  var userid108 = $('.userid')[108].value;}
if ( $('.nodeid')[109] ) { var nodeid109 = $('.nodeid')[109].value;	  var filename109 = $('.filename')[109].value;	  var userid109 = $('.userid')[109].value;}
if ( $('.nodeid')[110] ) { var nodeid110 = $('.nodeid')[110].value;	  var filename110 = $('.filename')[110].value;	  var userid110 = $('.userid')[110].value;}
if ( $('.nodeid')[111] ) { var nodeid111 = $('.nodeid')[111].value;	  var filename111 = $('.filename')[111].value;	  var userid111 = $('.userid')[111].value;}
if ( $('.nodeid')[112] ) { var nodeid112 = $('.nodeid')[112].value;	  var filename112 = $('.filename')[112].value;	  var userid112 = $('.userid')[112].value;}
if ( $('.nodeid')[113] ) { var nodeid113 = $('.nodeid')[113].value;	  var filename113 = $('.filename')[113].value;	  var userid113 = $('.userid')[113].value;}
if ( $('.nodeid')[114] ) { var nodeid114 = $('.nodeid')[114].value;	  var filename114 = $('.filename')[114].value;	  var userid114 = $('.userid')[114].value;}
if ( $('.nodeid')[115] ) { var nodeid115 = $('.nodeid')[115].value;	  var filename115 = $('.filename')[115].value;	  var userid115 = $('.userid')[115].value;}
if ( $('.nodeid')[116] ) { var nodeid116 = $('.nodeid')[116].value;	  var filename116 = $('.filename')[116].value;	  var userid116 = $('.userid')[116].value;}
if ( $('.nodeid')[117] ) { var nodeid117 = $('.nodeid')[117].value;	  var filename117 = $('.filename')[117].value;	  var userid117 = $('.userid')[117].value;}
if ( $('.nodeid')[118] ) { var nodeid118 = $('.nodeid')[118].value;	  var filename118 = $('.filename')[118].value;	  var userid118 = $('.userid')[118].value;}
if ( $('.nodeid')[119] ) { var nodeid119 = $('.nodeid')[119].value;	  var filename119 = $('.filename')[119].value;	  var userid119 = $('.userid')[119].value;}
if ( $('.nodeid')[120] ) { var nodeid120 = $('.nodeid')[120].value;	  var filename120 = $('.filename')[120].value;	  var userid120 = $('.userid')[120].value;}
if ( $('.nodeid')[121] ) { var nodeid121 = $('.nodeid')[121].value;	  var filename121 = $('.filename')[121].value;	  var userid121 = $('.userid')[121].value;}
if ( $('.nodeid')[122] ) { var nodeid122 = $('.nodeid')[122].value;	  var filename122 = $('.filename')[122].value;	  var userid122 = $('.userid')[122].value;}
if ( $('.nodeid')[123] ) { var nodeid123 = $('.nodeid')[123].value;	  var filename123 = $('.filename')[123].value;	  var userid123 = $('.userid')[123].value;}
if ( $('.nodeid')[124] ) { var nodeid124 = $('.nodeid')[124].value;	  var filename124 = $('.filename')[124].value;	  var userid124 = $('.userid')[124].value;}
if ( $('.nodeid')[125] ) { var nodeid125 = $('.nodeid')[125].value;	  var filename125 = $('.filename')[125].value;	  var userid125 = $('.userid')[125].value;}
if ( $('.nodeid')[126] ) { var nodeid126 = $('.nodeid')[126].value;	  var filename126 = $('.filename')[126].value;	  var userid126 = $('.userid')[126].value;}
if ( $('.nodeid')[127] ) { var nodeid127 = $('.nodeid')[127].value;	  var filename127 = $('.filename')[127].value;	  var userid127 = $('.userid')[127].value;}
if ( $('.nodeid')[128] ) { var nodeid128 = $('.nodeid')[128].value;	  var filename128 = $('.filename')[128].value;	  var userid128 = $('.userid')[128].value;}
if ( $('.nodeid')[129] ) { var nodeid129 = $('.nodeid')[129].value;	  var filename129 = $('.filename')[129].value;	  var userid129 = $('.userid')[129].value;}
if ( $('.nodeid')[130] ) { var nodeid130 = $('.nodeid')[130].value;	  var filename130 = $('.filename')[130].value;	  var userid130 = $('.userid')[130].value;}
if ( $('.nodeid')[131] ) { var nodeid131 = $('.nodeid')[131].value;	  var filename131 = $('.filename')[131].value;	  var userid131 = $('.userid')[131].value;}
if ( $('.nodeid')[132] ) { var nodeid132 = $('.nodeid')[132].value;	  var filename132 = $('.filename')[132].value;	  var userid132 = $('.userid')[132].value;}
if ( $('.nodeid')[133] ) { var nodeid133 = $('.nodeid')[133].value;	  var filename133 = $('.filename')[133].value;	  var userid133 = $('.userid')[133].value;}
if ( $('.nodeid')[134] ) { var nodeid134 = $('.nodeid')[134].value;	  var filename134 = $('.filename')[134].value;	  var userid134 = $('.userid')[134].value;}
if ( $('.nodeid')[135] ) { var nodeid135 = $('.nodeid')[135].value;	  var filename135 = $('.filename')[135].value;	  var userid135 = $('.userid')[135].value;}
if ( $('.nodeid')[136] ) { var nodeid136 = $('.nodeid')[136].value;	  var filename136 = $('.filename')[136].value;	  var userid136 = $('.userid')[136].value;}
if ( $('.nodeid')[137] ) { var nodeid137 = $('.nodeid')[137].value;	  var filename137 = $('.filename')[137].value;	  var userid137 = $('.userid')[137].value;}
if ( $('.nodeid')[138] ) { var nodeid138 = $('.nodeid')[138].value;	  var filename138 = $('.filename')[138].value;	  var userid138 = $('.userid')[138].value;}
if ( $('.nodeid')[139] ) { var nodeid139 = $('.nodeid')[139].value;	  var filename139 = $('.filename')[139].value;	  var userid139 = $('.userid')[139].value;}
if ( $('.nodeid')[140] ) { var nodeid140 = $('.nodeid')[140].value;	  var filename140 = $('.filename')[140].value;	  var userid140 = $('.userid')[140].value;}
if ( $('.nodeid')[141] ) { var nodeid141 = $('.nodeid')[141].value;	  var filename141 = $('.filename')[141].value;	  var userid141 = $('.userid')[141].value;}
if ( $('.nodeid')[142] ) { var nodeid142 = $('.nodeid')[142].value;	  var filename142 = $('.filename')[142].value;	  var userid142 = $('.userid')[142].value;}
if ( $('.nodeid')[143] ) { var nodeid143 = $('.nodeid')[143].value;	  var filename143 = $('.filename')[143].value;	  var userid143 = $('.userid')[143].value;}
if ( $('.nodeid')[144] ) { var nodeid144 = $('.nodeid')[144].value;	  var filename144 = $('.filename')[144].value;	  var userid144 = $('.userid')[144].value;}
if ( $('.nodeid')[145] ) { var nodeid145 = $('.nodeid')[145].value;	  var filename145 = $('.filename')[145].value;	  var userid145 = $('.userid')[145].value;}
if ( $('.nodeid')[146] ) { var nodeid146 = $('.nodeid')[146].value;	  var filename146 = $('.filename')[146].value;	  var userid146 = $('.userid')[146].value;}
if ( $('.nodeid')[147] ) { var nodeid147 = $('.nodeid')[147].value;	  var filename147 = $('.filename')[147].value;	  var userid147 = $('.userid')[147].value;}
if ( $('.nodeid')[148] ) { var nodeid148 = $('.nodeid')[148].value;	  var filename148 = $('.filename')[148].value;	  var userid148 = $('.userid')[148].value;}
if ( $('.nodeid')[149] ) { var nodeid149 = $('.nodeid')[149].value;	  var filename149 = $('.filename')[149].value;	  var userid149 = $('.userid')[149].value;}
if ( $('.nodeid')[150] ) { var nodeid150 = $('.nodeid')[150].value;	  var filename150 = $('.filename')[150].value;	  var userid150 = $('.userid')[150].value;}
if ( $('.nodeid')[151] ) { var nodeid151 = $('.nodeid')[151].value;	  var filename151 = $('.filename')[151].value;	  var userid151 = $('.userid')[151].value;}
if ( $('.nodeid')[152] ) { var nodeid152 = $('.nodeid')[152].value;	  var filename152 = $('.filename')[152].value;	  var userid152 = $('.userid')[152].value;}
if ( $('.nodeid')[153] ) { var nodeid153 = $('.nodeid')[153].value;	  var filename153 = $('.filename')[153].value;	  var userid153 = $('.userid')[153].value;}
if ( $('.nodeid')[154] ) { var nodeid154 = $('.nodeid')[154].value;	  var filename154 = $('.filename')[154].value;	  var userid154 = $('.userid')[154].value;}
if ( $('.nodeid')[155] ) { var nodeid155 = $('.nodeid')[155].value;	  var filename155 = $('.filename')[155].value;	  var userid155 = $('.userid')[155].value;}
if ( $('.nodeid')[156] ) { var nodeid156 = $('.nodeid')[156].value;	  var filename156 = $('.filename')[156].value;	  var userid156 = $('.userid')[156].value;}
if ( $('.nodeid')[157] ) { var nodeid157 = $('.nodeid')[157].value;	  var filename157 = $('.filename')[157].value;	  var userid157 = $('.userid')[157].value;}
if ( $('.nodeid')[158] ) { var nodeid158 = $('.nodeid')[158].value;	  var filename158 = $('.filename')[158].value;	  var userid158 = $('.userid')[158].value;}
if ( $('.nodeid')[159] ) { var nodeid159 = $('.nodeid')[159].value;	  var filename159 = $('.filename')[159].value;	  var userid159 = $('.userid')[159].value;}
if ( $('.nodeid')[160] ) { var nodeid160 = $('.nodeid')[160].value;	  var filename160 = $('.filename')[160].value;	  var userid160 = $('.userid')[160].value;}
if ( $('.nodeid')[161] ) { var nodeid161 = $('.nodeid')[161].value;	  var filename161 = $('.filename')[161].value;	  var userid161 = $('.userid')[161].value;}
if ( $('.nodeid')[162] ) { var nodeid162 = $('.nodeid')[162].value;	  var filename162 = $('.filename')[162].value;	  var userid162 = $('.userid')[162].value;}
if ( $('.nodeid')[163] ) { var nodeid163 = $('.nodeid')[163].value;	  var filename163 = $('.filename')[163].value;	  var userid163 = $('.userid')[163].value;}
if ( $('.nodeid')[164] ) { var nodeid164 = $('.nodeid')[164].value;	  var filename164 = $('.filename')[164].value;	  var userid164 = $('.userid')[164].value;}
if ( $('.nodeid')[165] ) { var nodeid165 = $('.nodeid')[165].value;	  var filename165 = $('.filename')[165].value;	  var userid165 = $('.userid')[165].value;}
if ( $('.nodeid')[166] ) { var nodeid166 = $('.nodeid')[166].value;	  var filename166 = $('.filename')[166].value;	  var userid166 = $('.userid')[166].value;}
if ( $('.nodeid')[167] ) { var nodeid167 = $('.nodeid')[167].value;	  var filename167 = $('.filename')[167].value;	  var userid167 = $('.userid')[167].value;}
if ( $('.nodeid')[168] ) { var nodeid168 = $('.nodeid')[168].value;	  var filename168 = $('.filename')[168].value;	  var userid168 = $('.userid')[168].value;}
if ( $('.nodeid')[169] ) { var nodeid169 = $('.nodeid')[169].value;	  var filename169 = $('.filename')[169].value;	  var userid169 = $('.userid')[169].value;}
if ( $('.nodeid')[170] ) { var nodeid170 = $('.nodeid')[170].value;	  var filename170 = $('.filename')[170].value;	  var userid170 = $('.userid')[170].value;}
if ( $('.nodeid')[171] ) { var nodeid171 = $('.nodeid')[171].value;	  var filename171 = $('.filename')[171].value;	  var userid171 = $('.userid')[171].value;}
if ( $('.nodeid')[172] ) { var nodeid172 = $('.nodeid')[172].value;	  var filename172 = $('.filename')[172].value;	  var userid172 = $('.userid')[172].value;}
if ( $('.nodeid')[173] ) { var nodeid173 = $('.nodeid')[173].value;	  var filename173 = $('.filename')[173].value;	  var userid173 = $('.userid')[173].value;}
if ( $('.nodeid')[174] ) { var nodeid174 = $('.nodeid')[174].value;	  var filename174 = $('.filename')[174].value;	  var userid174 = $('.userid')[174].value;}
if ( $('.nodeid')[175] ) { var nodeid175 = $('.nodeid')[175].value;	  var filename175 = $('.filename')[175].value;	  var userid175 = $('.userid')[175].value;}
if ( $('.nodeid')[176] ) { var nodeid176 = $('.nodeid')[176].value;	  var filename176 = $('.filename')[176].value;	  var userid176 = $('.userid')[176].value;}
if ( $('.nodeid')[177] ) { var nodeid177 = $('.nodeid')[177].value;	  var filename177 = $('.filename')[177].value;	  var userid177 = $('.userid')[177].value;}
if ( $('.nodeid')[178] ) { var nodeid178 = $('.nodeid')[178].value;	  var filename178 = $('.filename')[178].value;	  var userid178 = $('.userid')[178].value;}
if ( $('.nodeid')[179] ) { var nodeid179 = $('.nodeid')[179].value;	  var filename179 = $('.filename')[179].value;	  var userid179 = $('.userid')[179].value;}
if ( $('.nodeid')[180] ) { var nodeid180 = $('.nodeid')[180].value;	  var filename180 = $('.filename')[180].value;	  var userid180 = $('.userid')[180].value;}
if ( $('.nodeid')[181] ) { var nodeid181 = $('.nodeid')[181].value;	  var filename181 = $('.filename')[181].value;	  var userid181 = $('.userid')[181].value;}
if ( $('.nodeid')[182] ) { var nodeid182 = $('.nodeid')[182].value;	  var filename182 = $('.filename')[182].value;	  var userid182 = $('.userid')[182].value;}
if ( $('.nodeid')[183] ) { var nodeid183 = $('.nodeid')[183].value;	  var filename183 = $('.filename')[183].value;	  var userid183 = $('.userid')[183].value;}
if ( $('.nodeid')[184] ) { var nodeid184 = $('.nodeid')[184].value;	  var filename184 = $('.filename')[184].value;	  var userid184 = $('.userid')[184].value;}
if ( $('.nodeid')[185] ) { var nodeid185 = $('.nodeid')[185].value;	  var filename185 = $('.filename')[185].value;	  var userid185 = $('.userid')[185].value;}
if ( $('.nodeid')[186] ) { var nodeid186 = $('.nodeid')[186].value;	  var filename186 = $('.filename')[186].value;	  var userid186 = $('.userid')[186].value;}
if ( $('.nodeid')[187] ) { var nodeid187 = $('.nodeid')[187].value;	  var filename187 = $('.filename')[187].value;	  var userid187 = $('.userid')[187].value;}
if ( $('.nodeid')[188] ) { var nodeid188 = $('.nodeid')[188].value;	  var filename188 = $('.filename')[188].value;	  var userid188 = $('.userid')[188].value;}
if ( $('.nodeid')[189] ) { var nodeid189 = $('.nodeid')[189].value;	  var filename189 = $('.filename')[189].value;	  var userid189 = $('.userid')[189].value;}
if ( $('.nodeid')[190] ) { var nodeid190 = $('.nodeid')[190].value;	  var filename190 = $('.filename')[190].value;	  var userid190 = $('.userid')[190].value;}
if ( $('.nodeid')[191] ) { var nodeid191 = $('.nodeid')[191].value;	  var filename191 = $('.filename')[191].value;	  var userid191 = $('.userid')[191].value;}
if ( $('.nodeid')[192] ) { var nodeid192 = $('.nodeid')[192].value;	  var filename192 = $('.filename')[192].value;	  var userid192 = $('.userid')[192].value;}
if ( $('.nodeid')[193] ) { var nodeid193 = $('.nodeid')[193].value;	  var filename193 = $('.filename')[193].value;	  var userid193 = $('.userid')[193].value;}
if ( $('.nodeid')[194] ) { var nodeid194 = $('.nodeid')[194].value;	  var filename194 = $('.filename')[194].value;	  var userid194 = $('.userid')[194].value;}
if ( $('.nodeid')[195] ) { var nodeid195 = $('.nodeid')[195].value;	  var filename195 = $('.filename')[195].value;	  var userid195 = $('.userid')[195].value;}
if ( $('.nodeid')[196] ) { var nodeid196 = $('.nodeid')[196].value;	  var filename196 = $('.filename')[196].value;	  var userid196 = $('.userid')[196].value;}
if ( $('.nodeid')[197] ) { var nodeid197 = $('.nodeid')[197].value;	  var filename197 = $('.filename')[197].value;	  var userid197 = $('.userid')[197].value;}
if ( $('.nodeid')[198] ) { var nodeid198 = $('.nodeid')[198].value;	  var filename198 = $('.filename')[198].value;	  var userid198 = $('.userid')[198].value;}
if ( $('.nodeid')[199] ) { var nodeid199 = $('.nodeid')[199].value;	  var filename199 = $('.filename')[199].value;	  var userid199 = $('.userid')[199].value;}




  registerTabEvents();

  $('ul.recordTabs a').click(function (e) {
    if($(this).parents('li.active').length > 0) {
      return true;
    }
    var tabid = $(this).attr('id').toLowerCase();
    if($('#'+tabid+'-tab').length > 0) {
      $('#record-tabs .tab-pane.active').removeClass('active');
      $('#'+tabid+'-tab').addClass('active');
      $('#'+tabid).tab('show');
      return false;
    } else {
      $('#record-tabs').append('<div class="tab-pane" id="'+tabid+'-tab"><i class="fa fa-spinner fa-spin"></i> '+vufindString.loading+'...</div>');
      $('#record-tabs .tab-pane.active').removeClass('active');
      $('#'+tabid+'-tab').addClass('active');
      return ajaxLoadTab(tabid);
    }
  });

  /* --- LIGHTBOX --- */
  // Cite lightbox
  $('#cite-record').click(function() {
    var params = extractClassParams(this);
    return Lightbox.get(params['controller'], 'Cite', {id:id});
  });
  // Mail lightbox
  $('#mail-record0').click(function() {
    var params = extractClassParams(this);
    //SCB New params
    params.id = id;
    params.nodeid = nodeid;
    params.filename = filename;
    params.userid = userid;

    return Lightbox.get(params['controller'], 'Email', params);
  });

  $('#mail-recordkk').click(function() {
    var params = extractClassParams(this);
    //SCB New params
    params.id = id;
    params.nodeid = nodeid1;
    params.filename = filename1;
    params.userid = userid1;

    return Lightbox.get(params['controller'], 'Email', params);
  });

  $('#mail-record1').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid001;    params.filename = filename001;    params.userid = userid001;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record2').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid002;    params.filename = filename002;    params.userid = userid002;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record3').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid003;    params.filename = filename003;    params.userid = userid003;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record4').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid004;    params.filename = filename004;    params.userid = userid004;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record5').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid005;    params.filename = filename005;    params.userid = userid005;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record6').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid006;    params.filename = filename006;    params.userid = userid006;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record7').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid007;    params.filename = filename007;    params.userid = userid007;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record8').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid008;    params.filename = filename008;    params.userid = userid008;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record9').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid009;    params.filename = filename009;    params.userid = userid009;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record10').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid010;    params.filename = filename010;    params.userid = userid010;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record11').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid011;    params.filename = filename011;    params.userid = userid011;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record12').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid012;    params.filename = filename012;    params.userid = userid012;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record13').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid013;    params.filename = filename013;    params.userid = userid013;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record14').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid014;    params.filename = filename014;    params.userid = userid014;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record15').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid015;    params.filename = filename015;    params.userid = userid015;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record16').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid016;    params.filename = filename016;    params.userid = userid016;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record17').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid017;    params.filename = filename017;    params.userid = userid017;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record18').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid018;    params.filename = filename018;    params.userid = userid018;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record19').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid019;    params.filename = filename019;    params.userid = userid019;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record20').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid020;    params.filename = filename020;    params.userid = userid020;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record21').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid021;    params.filename = filename021;    params.userid = userid021;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record22').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid022;    params.filename = filename022;    params.userid = userid022;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record23').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid023;    params.filename = filename023;    params.userid = userid023;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record24').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid024;    params.filename = filename024;    params.userid = userid024;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record25').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid025;    params.filename = filename025;    params.userid = userid025;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record26').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid026;    params.filename = filename026;    params.userid = userid026;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record27').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid027;    params.filename = filename027;    params.userid = userid027;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record28').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid028;    params.filename = filename028;    params.userid = userid028;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record29').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid029;    params.filename = filename029;    params.userid = userid029;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record30').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid030;    params.filename = filename030;    params.userid = userid030;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record31').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid031;    params.filename = filename031;    params.userid = userid031;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record32').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid032;    params.filename = filename032;    params.userid = userid032;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record33').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid033;    params.filename = filename033;    params.userid = userid033;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record34').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid034;    params.filename = filename034;    params.userid = userid034;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record35').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid035;    params.filename = filename035;    params.userid = userid035;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record36').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid036;    params.filename = filename036;    params.userid = userid036;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record37').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid037;    params.filename = filename037;    params.userid = userid037;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record38').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid038;    params.filename = filename038;    params.userid = userid038;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record39').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid039;    params.filename = filename039;    params.userid = userid039;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record40').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid040;    params.filename = filename040;    params.userid = userid040;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record41').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid041;    params.filename = filename041;    params.userid = userid041;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record42').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid042;    params.filename = filename042;    params.userid = userid042;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record43').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid043;    params.filename = filename043;    params.userid = userid043;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record44').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid044;    params.filename = filename044;    params.userid = userid044;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record45').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid045;    params.filename = filename045;    params.userid = userid045;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record46').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid046;    params.filename = filename046;    params.userid = userid046;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record47').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid047;    params.filename = filename047;    params.userid = userid047;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record48').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid048;    params.filename = filename048;    params.userid = userid048;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record49').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid049;    params.filename = filename049;    params.userid = userid049;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record50').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid050;    params.filename = filename050;    params.userid = userid050;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record51').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid051;    params.filename = filename051;    params.userid = userid051;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record52').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid052;    params.filename = filename052;    params.userid = userid052;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record53').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid053;    params.filename = filename053;    params.userid = userid053;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record54').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid054;    params.filename = filename054;    params.userid = userid054;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record55').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid055;    params.filename = filename055;    params.userid = userid055;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record56').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid056;    params.filename = filename056;    params.userid = userid056;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record57').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid057;    params.filename = filename057;    params.userid = userid057;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record58').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid058;    params.filename = filename058;    params.userid = userid058;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record59').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid059;    params.filename = filename059;    params.userid = userid059;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record60').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid060;    params.filename = filename060;    params.userid = userid060;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record61').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid061;    params.filename = filename061;    params.userid = userid061;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record62').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid062;    params.filename = filename062;    params.userid = userid062;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record63').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid063;    params.filename = filename063;    params.userid = userid063;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record64').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid064;    params.filename = filename064;    params.userid = userid064;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record65').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid065;    params.filename = filename065;    params.userid = userid065;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record66').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid066;    params.filename = filename066;    params.userid = userid066;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record67').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid067;    params.filename = filename067;    params.userid = userid067;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record68').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid068;    params.filename = filename068;    params.userid = userid068;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record69').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid069;    params.filename = filename069;    params.userid = userid069;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record70').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid070;    params.filename = filename070;    params.userid = userid070;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record71').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid071;    params.filename = filename071;    params.userid = userid071;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record72').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid072;    params.filename = filename072;    params.userid = userid072;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record73').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid073;    params.filename = filename073;    params.userid = userid073;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record74').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid074;    params.filename = filename074;    params.userid = userid074;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record75').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid075;    params.filename = filename075;    params.userid = userid075;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record76').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid076;    params.filename = filename076;    params.userid = userid076;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record77').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid077;    params.filename = filename077;    params.userid = userid077;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record78').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid078;    params.filename = filename078;    params.userid = userid078;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record79').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid079;    params.filename = filename079;    params.userid = userid079;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record80').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid080;    params.filename = filename080;    params.userid = userid080;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record81').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid081;    params.filename = filename081;    params.userid = userid081;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record82').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid082;    params.filename = filename082;    params.userid = userid082;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record83').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid083;    params.filename = filename083;    params.userid = userid083;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record84').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid084;    params.filename = filename084;    params.userid = userid084;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record85').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid085;    params.filename = filename085;    params.userid = userid085;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record86').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid086;    params.filename = filename086;    params.userid = userid086;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record87').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid087;    params.filename = filename087;    params.userid = userid087;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record88').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid088;    params.filename = filename088;    params.userid = userid088;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record89').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid089;    params.filename = filename089;    params.userid = userid089;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record90').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid090;    params.filename = filename090;    params.userid = userid090;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record91').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid091;    params.filename = filename091;    params.userid = userid091;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record92').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid092;    params.filename = filename092;    params.userid = userid092;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record93').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid093;    params.filename = filename093;    params.userid = userid093;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record94').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid094;    params.filename = filename094;    params.userid = userid094;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record95').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid095;    params.filename = filename095;    params.userid = userid095;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record96').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid096;    params.filename = filename096;    params.userid = userid096;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record97').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid097;    params.filename = filename097;    params.userid = userid097;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record98').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid098;    params.filename = filename098;    params.userid = userid098;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record99').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid099;    params.filename = filename099;    params.userid = userid099;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record100').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid100;    params.filename = filename100;    params.userid = userid100;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record101').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid101;    params.filename = filename101;    params.userid = userid101;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record102').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid102;    params.filename = filename102;    params.userid = userid102;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record103').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid103;    params.filename = filename103;    params.userid = userid103;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record104').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid104;    params.filename = filename104;    params.userid = userid104;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record105').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid105;    params.filename = filename105;    params.userid = userid105;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record106').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid106;    params.filename = filename106;    params.userid = userid106;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record107').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid107;    params.filename = filename107;    params.userid = userid107;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record108').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid108;    params.filename = filename108;    params.userid = userid108;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record109').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid109;    params.filename = filename109;    params.userid = userid109;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record110').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid110;    params.filename = filename110;    params.userid = userid110;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record111').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid111;    params.filename = filename111;    params.userid = userid111;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record112').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid112;    params.filename = filename112;    params.userid = userid112;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record113').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid113;    params.filename = filename113;    params.userid = userid113;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record114').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid114;    params.filename = filename114;    params.userid = userid114;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record115').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid115;    params.filename = filename115;    params.userid = userid115;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record116').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid116;    params.filename = filename116;    params.userid = userid116;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record117').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid117;    params.filename = filename117;    params.userid = userid117;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record118').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid118;    params.filename = filename118;    params.userid = userid118;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record119').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid119;    params.filename = filename119;    params.userid = userid119;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record120').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid120;    params.filename = filename120;    params.userid = userid120;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record121').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid121;    params.filename = filename121;    params.userid = userid121;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record122').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid122;    params.filename = filename122;    params.userid = userid122;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record123').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid123;    params.filename = filename123;    params.userid = userid123;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record124').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid124;    params.filename = filename124;    params.userid = userid124;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record125').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid125;    params.filename = filename125;    params.userid = userid125;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record126').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid126;    params.filename = filename126;    params.userid = userid126;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record127').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid127;    params.filename = filename127;    params.userid = userid127;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record128').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid128;    params.filename = filename128;    params.userid = userid128;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record129').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid129;    params.filename = filename129;    params.userid = userid129;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record130').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid130;    params.filename = filename130;    params.userid = userid130;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record131').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid131;    params.filename = filename131;    params.userid = userid131;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record132').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid132;    params.filename = filename132;    params.userid = userid132;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record133').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid133;    params.filename = filename133;    params.userid = userid133;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record134').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid134;    params.filename = filename134;    params.userid = userid134;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record135').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid135;    params.filename = filename135;    params.userid = userid135;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record136').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid136;    params.filename = filename136;    params.userid = userid136;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record137').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid137;    params.filename = filename137;    params.userid = userid137;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record138').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid138;    params.filename = filename138;    params.userid = userid138;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record139').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid139;    params.filename = filename139;    params.userid = userid139;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record140').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid140;    params.filename = filename140;    params.userid = userid140;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record141').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid141;    params.filename = filename141;    params.userid = userid141;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record142').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid142;    params.filename = filename142;    params.userid = userid142;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record143').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid143;    params.filename = filename143;    params.userid = userid143;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record144').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid144;    params.filename = filename144;    params.userid = userid144;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record145').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid145;    params.filename = filename145;    params.userid = userid145;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record146').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid146;    params.filename = filename146;    params.userid = userid146;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record147').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid147;    params.filename = filename147;    params.userid = userid147;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record148').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid148;    params.filename = filename148;    params.userid = userid148;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record149').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid149;    params.filename = filename149;    params.userid = userid149;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record150').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid150;    params.filename = filename150;    params.userid = userid150;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record151').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid151;    params.filename = filename151;    params.userid = userid151;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record152').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid152;    params.filename = filename152;    params.userid = userid152;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record153').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid153;    params.filename = filename153;    params.userid = userid153;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record154').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid154;    params.filename = filename154;    params.userid = userid154;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record155').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid155;    params.filename = filename155;    params.userid = userid155;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record156').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid156;    params.filename = filename156;    params.userid = userid156;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record157').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid157;    params.filename = filename157;    params.userid = userid157;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record158').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid158;    params.filename = filename158;    params.userid = userid158;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record159').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid159;    params.filename = filename159;    params.userid = userid159;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record160').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid160;    params.filename = filename160;    params.userid = userid160;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record161').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid161;    params.filename = filename161;    params.userid = userid161;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record162').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid162;    params.filename = filename162;    params.userid = userid162;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record163').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid163;    params.filename = filename163;    params.userid = userid163;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record164').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid164;    params.filename = filename164;    params.userid = userid164;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record165').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid165;    params.filename = filename165;    params.userid = userid165;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record166').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid166;    params.filename = filename166;    params.userid = userid166;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record167').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid167;    params.filename = filename167;    params.userid = userid167;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record168').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid168;    params.filename = filename168;    params.userid = userid168;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record169').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid169;    params.filename = filename169;    params.userid = userid169;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record170').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid170;    params.filename = filename170;    params.userid = userid170;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record171').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid171;    params.filename = filename171;    params.userid = userid171;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record172').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid172;    params.filename = filename172;    params.userid = userid172;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record173').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid173;    params.filename = filename173;    params.userid = userid173;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record174').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid174;    params.filename = filename174;    params.userid = userid174;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record175').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid175;    params.filename = filename175;    params.userid = userid175;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record176').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid176;    params.filename = filename176;    params.userid = userid176;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record177').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid177;    params.filename = filename177;    params.userid = userid177;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record178').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid178;    params.filename = filename178;    params.userid = userid178;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record179').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid179;    params.filename = filename179;    params.userid = userid179;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record180').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid180;    params.filename = filename180;    params.userid = userid180;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record181').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid181;    params.filename = filename181;    params.userid = userid181;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record182').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid182;    params.filename = filename182;    params.userid = userid182;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record183').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid183;    params.filename = filename183;    params.userid = userid183;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record184').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid184;    params.filename = filename184;    params.userid = userid184;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record185').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid185;    params.filename = filename185;    params.userid = userid185;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record186').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid186;    params.filename = filename186;    params.userid = userid186;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record187').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid187;    params.filename = filename187;    params.userid = userid187;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record188').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid188;    params.filename = filename188;    params.userid = userid188;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record189').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid189;    params.filename = filename189;    params.userid = userid189;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record190').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid190;    params.filename = filename190;    params.userid = userid190;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record191').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid191;    params.filename = filename191;    params.userid = userid191;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record192').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid192;    params.filename = filename192;    params.userid = userid192;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record193').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid193;    params.filename = filename193;    params.userid = userid193;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record194').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid194;    params.filename = filename194;    params.userid = userid194;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record195').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid195;    params.filename = filename195;    params.userid = userid195;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record196').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid196;    params.filename = filename196;    params.userid = userid196;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record197').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid197;    params.filename = filename197;    params.userid = userid197;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record198').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid198;    params.filename = filename198;    params.userid = userid198;    return Lightbox.get(params['controller'], 'Email', params);  });
  $('#mail-record199').click(function() {    var params = extractClassParams(this);    params.id = id;    params.nodeid = nodeid199;    params.filename = filename199;    params.userid = userid199;    return Lightbox.get(params['controller'], 'Email', params);  });



  // Save lightbox
  $('#save-record').click(function() {
    var params = extractClassParams(this);
    return Lightbox.get(params['controller'], 'Save', {id:id});
  });
  // SMS lightbox
  $('#sms-record').click(function() {
    var params = extractClassParams(this);
    return Lightbox.get(params['controller'], 'SMS', {id:id});
  });
  // Tag lightbox
  $('#tagRecord').click(function() {
    var id = $('.hiddenId')[0].value;
    var parts = this.href.split('/');
    Lightbox.addCloseAction(function() {
      var recordId = $('#record_id').val();
      var recordSource = $('.hiddenSource').val();

      // Update tag list (add tag)
      var tagList = $('#tagList');
      if (tagList.length > 0) {
        tagList.empty();
        var url = path + '/AJAX/JSON?' + $.param({method:'getRecordTags',id:recordId,'source':recordSource});
        $.ajax({
          dataType: 'json',
          url: url,
          success: function(response) {
            if (response.status == 'OK') {
              $.each(response.data, function(i, tag) {
                var href = path + '/Tag?' + $.param({lookfor:tag.tag});
                var html = (i>0 ? ', ' : ' ') + '<a href="' + htmlEncode(href) + '">' + htmlEncode(tag.tag) +'</a> (' + htmlEncode(tag.cnt) + ')';
                tagList.append(html);
              });
            } else if (response.data && response.data.length > 0) {
              tagList.append(response.data);
            }
          }
        });
      }
    });
    return Lightbox.get(parts[parts.length-3],'AddTag',{id:id});
  });
  // Form handlers
  Lightbox.addFormCallback('smsRecord', function(){Lightbox.confirm(vufindString['sms_success']);});
  Lightbox.addFormCallback('emailRecord', function(){
    Lightbox.confirm(vufindString['bulk_email_success']);
  });
  Lightbox.addFormCallback('saveRecord', function(){
    checkSaveStatuses();
    Lightbox.confirm(vufindString['bulk_save_success']);
  });
  Lightbox.addFormCallback('placeHold', function(html) {
    Lightbox.checkForError(html, function(html) {
      var divPattern = '<div class="alert alert-info">';
      var fi = html.indexOf(divPattern);
      var li = html.indexOf('</div>', fi+divPattern.length);
      Lightbox.confirm(html.substring(fi+divPattern.length, li).replace(/^[\s<>]+|[\s<>]+$/g, ''));
    });
  });
  Lightbox.addFormCallback('placeStorageRetrievalRequest', function() {
    document.location.href = path+'/MyResearch/StorageRetrievalRequests';
  });
  Lightbox.addFormCallback('placeILLRequest', function() {
    document.location.href = path+'/MyResearch/ILLRequests';
  });
});
