$(document).ready(function () {
    $("#cab_show_proc").hide();
    $("#cab_show_login").hide();
    $("#ListaTemperatura").hide();

    //$("#cab_procurar").hide();
   // $("#cab_login").hide();

    /*$("#procurar").toggle(function()) {
        $("#procurar").addClass("procurar_ativo");
    });*/



    $("#procurar").click(function (evento) {
        $("#procurar").toggleClass("procurar_ativo");
        $("#login").removeClass('procurar_ativo');
        $("#cab_show_proc").slideToggle();
        $("#cab_show_login").hide();
    });

    $("#login").click(function () {
        $("#login").toggleClass("procurar_ativo");
        $("#procurar").removeClass('procurar_ativo');
        $("#cab_show_proc").hide();
        $("#cab_show_login").slideToggle();
    });

    $("#selCidade").click(function (evento){
        $("#ListaTemperatura").slideToggle();
    });

});