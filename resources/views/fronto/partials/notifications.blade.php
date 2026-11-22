<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof iziToast === 'undefined') return;

    @if(session('success'))
        iziToast.success({
            title: 'Succès',
            message: @json(session('success')),
            position: 'topRight',
            timeout: 6000
        });
    @endif

    @if(session('error'))
        iziToast.error({
            title: 'Erreur',
            message: @json(session('error')),
            position: 'topRight',
            timeout: 7000
        });
    @endif

    @if($errors->any())
        iziToast.error({
            title: 'Vérifiez votre formulaire',
            message: @json(implode(' ', $errors->all())),
            position: 'topRight',
            timeout: 7000
        });
    @endif
});
</script>
