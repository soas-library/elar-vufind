/*SCB; Active the current tabs from collection view*/
$(document).ready(function () {
    if(window.location.href.indexOf("type") > -1 || window.location.href.indexOf("lookfor") > -1 || window.location.href.indexOf("#items") > -1) {
       activaTab('items');
    }
});

function activaTab(tab){
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

/* When user change the tab, update the breadcrumbs */

$(document).on( 'shown.bs.tab', 'a[href="#items"]', function (e) {
   if(window.location.href.indexOf("#items") == -1) {
       window.location.href = window.location.href + "#items";
    }
});

$(document).on( 'shown.bs.tab', 'a[href="#deposit"]', function (e) {
   if(window.location.href.indexOf("#items") > -1) {
       window.location.hash = "";
       // remove fragment as much as it can go without adding an entry in browser history:
	window.location.replace("#");
	
	// slice off the remaining '#' in HTML5:    
	if (typeof window.history.replaceState == 'function') {
	  history.replaceState({}, '', window.location.href.slice(0, -1));
	}
    }
});