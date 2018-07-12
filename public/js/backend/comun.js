function _mensaje(id, texto){

	$(id).html(texto); 
    
    $(id).dialog({
        width: "auto",
        height: "auto",
        title: "¡Atención!",
        resizable: false,
        draggable:false,
        modal: true,
        buttons: [
            {
                id: "aceptar-999",
                text : "Aceptar",
                class: "btn btn-rojo-1",
                click: function(){

                    $(id).dialog("close");
                }
            }
        ] //buttons
    }); //dialog
}

function cargaPreview(id, ruta){

    $(id).html('<img src="http://'+window.location.hostname+ruta+'" class="col-xs-12" />'); 
                        
    $(id).dialog({
        width: "800",
        height: "auto",
        title: "Vista Previa",
        resizable: false,
        draggable:false,
        modal: true,
        buttons: [
            {
                id: "cerrar-999",
                text : "Cerrar",
                class: "btn btn-rojo-1",
                click: function(){

                    $(id).dialog("close");
                }
            }
        ] //buttons
    }); //dialog
}

function _mensajeFuncion(id, texto, funcion){
	$(id).html(texto); 
                        
    $(id).dialog({
        width: "auto",
        height: "auto",
        title: "¡Atención!",
        resizable: false,
        draggable:false,
        modal: true,
        buttons: [
            {
                id: "aceptar-999",
                text : "Aceptar",
                class: "btn btn-rojo-1",
                click: function(){
                    funcion();
                    $(id).dialog("close");
                }
            }
        ] //buttons
    }); //dialog
}

function _confirmar(id, texto, funcion){

    texto = "<div id=\"es-999\" name=\"es-999\" class=\"alert alert-success hide\" style=\"padding-bottom: 0;\" role=\"alert\">Espere un momento, por favor...<div class=\"progress\"><div class=\"progress-bar progress-bar-success progress-bar-striped active\"  role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\"><span class=\"sr-only\">100% Completo</span></div></div></div><div id=\"texto-999\" name=\"texto-999\">" + texto + "</div>";

    $(id).html(texto);
    $(id).dialog({
        height: "auto"
        ,width: "auto"
        ,resizable: false
        ,title: "Confirmación"
        ,draggable:false
        ,modal: true
        ,buttons: [
            {
                id: "si-999"
                ,text: "Sí"
                ,class: "btn btn-rojo-1"
                ,click: function(){

                    funcion();                    
                }
            }
            ,{
                id: "no-999"
                ,text: "No"
                ,class: "btn btn-rojo-1"
                ,click: function(){

                    $(id).dialog("close");
                }
            }
        ] //buttons
    }); //dialog
}//function


function _usuario(id){

    $.ajax({
        type: "POST"
        ,url: "/usuario/mis-datos"
        ,data: {
            id: id
        }
        ,success: function(html){
            
            $("#_dialogo-1").html(html);

            $("#frm-1").validate({                
                submitHandler: function(form){
                    
                    $("#es-1").removeClass('hide');
                    $("#frm-1").addClass('hide');
                    $("#aceptar-1").addClass('hide');
                    $("#cancelar-1").addClass('hide');
                    
                    $(form).ajaxSubmit({
                        success: function(respuesta){
                            
                            if(isNaN(respuesta)){

                                $("#es-1").addClass('hide');
                                $("#frm-1").removeClass('hide');
                                $("#aceptar-1").removeClass('hide');
                                $("#cancelar-1").removeClass('hide');
                                
                                _mensaje("#_mensaje-1", respuesta);
                            }else{

                                $("#_dialogo-1").dialog("close");
                            }
                        } //success
                        ,error: function(respuesta){

                            $("#es-1").addClass('hide');
                            $("#frm-1").removeClass('hide');
                            $("#aceptar-1").removeClass('hide');
                            $("#cancelar-1").removeClass('hide');

                            _mensaje("#_mensaje-1", "Ocurri&oacute; un error al tratar de guardar el usuario, int&eacute;ntelo de nuevo");
                        } //error
                    }) //ajaxSubmit
                } //submitHandler
            }) //validate
            
            $("#_dialogo-1").dialog({
                width: "400"
                ,height: "auto"
                ,title: "Usuario"
                ,resizable: false
                ,draggable:false
                ,modal: true
                ,buttons: [
                ] //buttons
            }) //dialog
        } //success
        ,error: function(respuesta){

            _mensaje("#_mensaje-1", "Ocurri&oacute; un error al tratar de abrir la ventana, int&eacute;ntelo de nuevo");
        } //error
    }) //$.ajax
}

function guardar_usuario(){
    $("#frm-1").validate({
        submitHandler: function(form){
            $(form).ajaxSubmit({
                success: function(respuesta){
                    if(!isNaN(respuesta)){
                        $("#_dialogo-1").dialog("close");
                    }else{
                        _mensaje("#_mensaje-1",  respuesta );
                    }
                } //success
                ,error: function(respuesta){
                    _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
                } //error
            }) //ajaxSubmit
        } //submitHandler
    }) //validate
    
    $("#frm-1").submit();
}

function permiteNumerosConDecimal(evt, obj) {
    
    var charCode = (evt.which) ? evt.which : event.keyCode
    var value = obj.value;

    if(charCode==127 ||charCode==37 ||charCode==39)
        return true;
        
    var dotcontains = value.indexOf(".") != -1;
    if (dotcontains)
        if (charCode == 46) return false;
    if (charCode == 46 && value!='') return true;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;

}

function permiteNumerosSinDecimal(evt, obj) {
    
    var charCode = (evt.which) ? evt.which : event.keyCode
    var value = obj.value;
    if(charCode==127 ||charCode==37 ||charCode==39)
        return true;
    var dotcontains = value.indexOf(".") != 1;
    if (dotcontains)
        if (charCode == 46) return false;
    if (charCode == 46) return true;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;

}

function cancelar() {
    $("#_dialogo-1").dialog("close");
}

function agregar(urlDestino, identificador, form, titulo) {
    $.ajax({
        type: "POST",
        url: urlDestino,
        data: {
            id: identificador
        }
        ,success: function(html)
        {
            $("#_dialogo-1").html(html);        
            $("#_dialogo-1").dialog({
                width: "900",
                height: "auto",
                title: titulo,
                resizable: false,
                draggable: false,
                modal: true
            })//dialog
            
        }//success
        ,error: function(respuesta){
            _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
        } //error
    }) //$.ajax

}

/**
 * @function     guardar
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function guardar (formulario, frmFiltro, filtroinicial, urlImprimir, urlExportar) {
	
	$("#"+formulario).validate({
		submitHandler: function(frm) {
			$(frm).ajaxSubmit({
				success: function(respuesta){
					if(!isNaN(respuesta)) {
						// _mensaje('#_mensaje-1', respuesta);
						_mensaje('#_mensaje-1', 'Se guardó el registro de forma correcta');
						recargar(formulario);
					}
					else {
						_mensaje("#_mensaje-1",  respuesta );
					}
				}
				,error: function(respuesta){
					_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
				}
			})
		}
	})
	
	$("#"+formulario).submit();
}

/**
 * @function     recargar
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function recargar (formularioDeGuardado) {
	$("#flexigrid").flexReload();
	cancelar();
}

/**
 * @function     limpiar
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function limpiar (formulario, filtroinicial) {
	console.log(formulario);
	$("#"+formulario)[0].reset();
	filtrar(formulario, filtroinicial);
}

/**
 * @function     filtrar
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function filtrar (formulario, filtroinicial, urlImprimir, urlExportar) {
	
	var filtro = '';
	
	$("#"+formulario+" :input").each(function(){
		if(this.id!='' && $("#"+this.id).val()!='')
			filtro+="/"+this.name+"/"+$("#"+this.id).val();
	});
	
	$("#btnImprimir").attr("href",urlImprimir+filtro);
	$("#btnExportar").attr("href",urlExportar+filtro);
	
	$("#flexigrid").flexOptions({
		url: filtroinicial+filtro,
		onSuccess: function(){
			
		}
	}).flexReload();
}

/**
 * @function     eliminar
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function eliminar (id, estatus, formulario) {
	
	if (id != '') {
		_confirmar(
			"#_mensaje-1",
			"¿Est&aacute; seguro que quiere "+(estatus == 1 ? "eliminar" : "activar")+" el registro?",
			function() {
				
				$("#es_mensaje-1").removeClass("hide");
				$("#texto_mensaje-1").addClass("hide");
				$("#si-999").addClass('hide');
				$("#no-999").addClass('hide');
				$.ajax({
					type: "POST",
					url: urlEliminar,
					data: {
						id: id
					},
					success: function(respuesta) {
						recargar(formulario);
						_mensaje("#_mensaje-1", respuesta);
					},
					error: function(respuesta){
						_mensaje("#_mensaje-1", "Ocurri&oacute; un error al tratar de eliminar el registro, int&eacute;ntelo de nuevo");
					} //error
				});
			}
		);
	}
}
