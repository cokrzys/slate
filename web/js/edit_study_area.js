
$( document ).ajaxStop(function() {
//--------------------------------------------------------------------------  
  console.log('DEBUG: AJAX stop handler fired.');
});

function getDefaults(source_file_rowid_fk) {
//--------------------------------------------------------------------------   
  var url = 'ajax_get_shapefile_extents.php?source_file_rowid_fk=' + source_file_rowid_fk;
  jQuery.ajax({
    'url' : url,
    'type' : 'post',
    'dataType' : 'json',
    'cache' : false,
    'success' : function(data) {
      if (data.status == 'success') {
      	console.log('DEBUG: Back with success.');
      	$("#min_x").val(JSON.parse(data.min_x));
      	$("#max_x").val(JSON.parse(data.max_x));
      	$("#min_y").val(JSON.parse(data.min_y));
      	$("#max_y").val(JSON.parse(data.max_y));
      	$("#srid_fk").val(JSON.parse(data.epsg));
      }
    }
  });
  return false;
};

function setupDefaults() {
//--------------------------------------------------------------------------
	console.log('DEBUG: In setupDefaults().');
	
	var source_file_rowid_fk = $("#source_file_dex_dot_rowid").val();
	
	console.log('DEBUG: source_file_dex_dot_rowid = ' + source_file_rowid_fk);
	
	if (source_file_rowid_fk.length > 0) {
		console.log('DEBUG: Have everything we need.');
		getDefaults(source_file_rowid_fk);
	}
	
}