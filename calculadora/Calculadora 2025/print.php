<div role="main" id="calculadora-pt-3763f31bbd445ebee4cd"></div>
<script type="text/javascript" src="https://d335luupugsy2.cloudfront.net/js/rdstation-forms/stable/rdstation-forms.min.js"></script>
<script type="text/javascript">
  new RDStationForms('calculadora-pt-3763f31bbd445ebee4cd-html', 'UA-60829383-1').createForm();

  
</script>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"
			  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
			  crossorigin="anonymous"></script>

<script type="text/javascript">
	$(function() {
		 
		$("#custom_fields_335618").val('<?php echo $_REQUEST['valor'];?>');


		$("#cf_submit-calculadora-pt-3763f31bbd445ebee4cd").click(function(){
		var email = $("#email").val();

		$.post("manda-email.php", {id: '<?php echo $_REQUEST['id'];?>', email: email},function(data){	
			
			//alert(data);

		});	


	})



		 })

</script>

