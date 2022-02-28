$("#registration_form_beOrganisateur" ).click(function() {
	if($('#bloc_address').hasClass('d-none'))
	{
		$('#bloc_address').removeClass('d-none');
		$('#bloc_address').show("slow");
	}
	else
	{
		$('#bloc_address').hide("slow", function(){
			$('#bloc_address').addClass('d-none');
		});
	}
});