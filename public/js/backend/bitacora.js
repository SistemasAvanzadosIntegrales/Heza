
/**
 * Documents Ready
 **/
$(document).ready(function() {
	
	if($("#fusuarioId").val()  != "") filtro+="/usuario/"+$("#fnombre").val();
	if($("#fmodelo").val()     != "") filtro+="/modelo/"+$("#fmodelo").val();
	if($("#faccion").val()     != "") filtro+="/accion/"+$("#faccion").val();
	if($("#freferencia").val() != "") filtro+="/referencia/"+$("#freferencia").val();
	if($("#fdesde").val()      != "") filtro+="/desde/"+$("#fdesde").val();
	if($("#fhasta").val()      != "") filtro+="/hasta/"+$("#fhasta").val();
	
	$("#fdesde").datepicker({
		dateFormat: "yy-mm-dd"
		,changeMonth: true
		,changeYear: true
		,maxDate: "+0d"
	});
	
	$("#fhasta").datepicker({
		dateFormat: "yy-mm-dd"
		,changeMonth: true
		,changeYear: true
		,maxDate: "+0d"
	});
	
	$("#flexigrid").flexigrid({
		
		url: "/bitacora/grid"
		,dataType: "xml"
		,colModel: [
			 {display: "Fecha",         name: "updated_at", width: 200, sortable: true, align:  "center"}
			,{display: "Usuario",       name: "usuario",    width: 250, sortable: false, align: "center"}
			,{display: "M&oacute;dulo", name: "modelo",     width: 190, sortable: true, align:  "center"}
			,{display: "Acci&oacute;n", name: "accion",     width: 300, sortable: true, align:  "center"}
			,{display: "Referencia",    name: "referencia", width: 200, sortable: true, align:  "center"}
			,{display: "Id",            name: "bit_id",     width: 50,  sortable: false, align: "center"}
		]
		,sortname: "updated_at"
		,sortorder: "desc"
		,usepager: true
		,useRp: false
		,singleSelect: true
		,resizable: false
		,showToggleBtn: false
		,rp: 10
		,width: 1200
		,height: 300
	});
});