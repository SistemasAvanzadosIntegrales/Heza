urlEliminar = '/ejercicio/eliminar';

$(document).ready(function(){
	cargaGridfrmEjercicio();
});

/**
 * cargaGridfrmEjercicio
 **/
function cargaGridfrmEjercicio() {
	
	var filtro="/ejercicio/grid";
	
	if($("#fnombre").val() != "" ) filtro+="/nombre/"+$("#fnombre").val();
	if($("#fstatus").val() != "" ) filtro+="/status/"+$("#fstatus").val();
		
	$("#flexigrid").flexigrid({
		url: filtro,
		dataType: "xml",
		colModel: [
			{display: "Nombre",  name:"nombre", width: 400, sortable: true, align: "center"},
			{display: "Estatus", name:"status", width: 265, sortable: true, align: "center"}
		],
		sortname: "nombre"
		,sortorder: "asc"
		,usepager: true
		,useRp: false
		,singleSelect: true
		,resizable: false
		,showToggleBtn: false
		,rp: 10
		,width: "auto"
		,height: "auto"
		,onSuccess: function(){
			$("#flexigrid tr").bind('click',function(){
				
				//obtenemos el id del ejercicio
				var idEjercicio = $(this).find(".registro").attr("rel").valueOf();
				$("#id").val(idEjercicio);
				console.log(idEjercicio);
				obtenerEjercicio(idEjercicio);
			});
		}
	});
}

/**
 * obtenerEjercicio
 **/
function obtenerEjercicio (id) {
	
	$.ajax({
		type: "POST"
		,url: "/ejercicio/obtener"
		,dataType: "json"
		,processData:true
		,data: {id:id }
		,success: function(re){
			console.log("Servidor: ");
			console.log(re);
			if(re != '' && re != undefined && re.error == ''){
				$("#nombre").val(re.nombre);
				$("#status").val(re.status).change();
				$("#eliminar").attr("onclick","eliminar('"+re.id+"','"+re.status+"','frmEjercicio')");
			}
			else {
				_mensaje("#_mensaje-1", re.error);
				if(re.filtrado != '' && re.filtrado != undefined){
					if(peticion == "eliminar-servicios"){
						limpiarServicio('0');
					}
				}
			}
		}//success
		,error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
		} //error
	})
}

/**
 * limpiarfrmEjercicio
 **/
function limpiarfrmEjercicio() {
	
	$("#frmEjercicio")[0].reset();
	$("#eliminar").attr("onclick","eliminar('', '1')");
	$("#id").val('');
}