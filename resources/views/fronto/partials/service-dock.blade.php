@php
    $serviceChamber = $category ?? ($article->category ?? null);
    $chamberCatalogue = $serviceChamber
        ? config('public_services.chambers.'.$serviceChamber->slug)
        : null;
    $serviceChamberName = $chamberCatalogue['name'] ?? ($serviceChamber->name ?? 'cette chambre');
    $chamberServices = $chamberCatalogue['services'] ?? [];
@endphp

<aside class="public-service-dock" aria-label="Demander un service">
    <button class="public-service-trigger" type="button" aria-label="Nos services pour {{ $serviceChamberName }}" aria-haspopup="dialog" aria-controls="publicServiceModal">
        <i class="fas fa-concierge-bell" aria-hidden="true"></i>
        <span>Nos services pour {{ $serviceChamberName }}</span>
    </button>
</aside>

<div class="public-service-backdrop" id="publicServiceModal" role="dialog" aria-modal="true" aria-labelledby="publicServiceTitle" hidden>
    <section class="public-service-modal">
        <button class="public-service-close" type="button" aria-label="Fermer">&times;</button>
        <div class="public-service-modal-heading">
            <span class="public-service-eyebrow">EUNITAL · À votre service</span>
            <h2 id="publicServiceTitle">Nos services pour {{ $serviceChamberName }}</h2>
            <p>{{ $chamberCatalogue['mission'] ?? 'Choisissez une prestation de la chambre '.$serviceChamberName.' pour nous transmettre votre besoin.' }}</p>
        </div>

        <div class="public-service-picker">
            @if($chamberServices)
                <div class="public-service-grid">
                @foreach($chamberServices as $serviceKey => $service)
                    <button class="public-service-option" type="button"
                        data-service-key="{{ $serviceKey }}"
                        data-service-label="{{ $service['label'] }}">
                        <span class="public-service-icon"><i class="fas fa-{{ $service['icon'] }}" aria-hidden="true"></i></span>
                        <span><small>{{ $serviceChamberName }}</small><strong>{{ $service['label'] }}</strong></span>
                        <i class="fas fa-arrow-right public-service-arrow" aria-hidden="true"></i>
                    </button>
                @endforeach
                </div>
            @else
                <p class="public-service-empty">Le catalogue des prestations de cette chambre sera bientôt disponible.</p>
            @endif
        </div>

        <div class="public-service-request" hidden>
            <button type="button" class="public-service-back"><i class="fas fa-arrow-left"></i> Toutes les prestations de la chambre</button>
            <div class="public-service-selected">
                <span class="public-service-icon"><i class="fas fa-paper-plane"></i></span>
                <div><small>Votre demande concerne</small><strong class="public-service-selected-name"></strong></div>
            </div>
            <form class="public-service-form" action="{{ route('public.services.request') }}" method="POST">
                @csrf
                <input type="hidden" name="chamber_slug" value="{{ $serviceChamber->slug ?? '' }}">
                <input type="hidden" name="service_key">
                <div class="public-service-form-grid">
                    <label>Votre nom *
                        <input type="text" name="name" maxlength="150" autocomplete="name" required>
                    </label>
                    <label>Votre email *
                        <input type="email" name="email" maxlength="255" autocomplete="email" required>
                    </label>
                    <label>Téléphone *
                        <input type="tel" name="phone" maxlength="40" autocomplete="tel" required>
                    </label>
                    <label>Organisation <span>(facultatif)</span>
                        <input type="text" name="organization" maxlength="180" autocomplete="organization">
                    </label>
                    <label class="public-service-wide">Décrivez votre besoin *
                        <textarea name="details" rows="4" maxlength="5000" required placeholder="Précisez votre projet, les quantités, les délais ou toute information utile..."></textarea>
                    </label>
                </div>
                <div class="public-service-feedback" role="status" aria-live="polite"></div>
                <div class="public-service-form-actions">
                    <button class="public-service-submit" type="submit"><span>Envoyer ma demande</span><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
    body.public-service-modal-open{overflow:hidden}
    .public-service-dock{position:fixed;z-index:1030;left:0;top:50%;transform:translateY(-50%);font-family:inherit}
    .public-service-trigger{height:58px;width:auto;min-width:58px;max-width:calc(100vw - 24px);padding:0 17px;border:0;border-radius:0 16px 16px 0;background:linear-gradient(145deg,#173b58,#087f83);color:#fff;display:flex;align-items:center;gap:14px;box-shadow:0 8px 26px #102e4538;cursor:pointer;overflow:hidden;white-space:nowrap;transition:padding .28s ease,background .2s ease,box-shadow .2s ease}
    .public-service-trigger i{font-size:21px;min-width:24px;text-align:center}
    .public-service-trigger span{max-width:0;font-size:14px;font-weight:700;opacity:0;overflow:hidden;transform:translateX(-8px);transition:max-width .28s ease,opacity .2s ease,transform .2s ease}
    .public-service-dock:hover .public-service-trigger,.public-service-dock:focus-within .public-service-trigger{background:linear-gradient(110deg,#173b58,#078b88);box-shadow:0 10px 30px #102e4548}
    .public-service-dock:hover .public-service-trigger span,.public-service-dock:focus-within .public-service-trigger span{max-width:calc(100vw - 100px);opacity:1;transform:translateX(0)}
    .public-service-trigger:focus-visible,.public-service-close:focus-visible,.public-service-option:focus-visible{outline:3px solid #edc975;outline-offset:3px}
    .public-service-backdrop{position:fixed;z-index:1050;inset:0;background:rgba(9,24,39,.66);backdrop-filter:blur(5px);display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity .2s ease}
    .public-service-backdrop[hidden]{display:none}
    .public-service-backdrop.is-open{opacity:1}
    .public-service-modal{position:relative;width:min(760px,100%);max-height:min(88vh,900px);overflow:auto;background:#fff;border-radius:24px;padding:clamp(24px,5vw,46px);box-shadow:0 25px 90px #06182b55;transform:translateY(16px) scale(.985);transition:transform .24s ease}
    .public-service-backdrop.is-open .public-service-modal{transform:translateY(0) scale(1)}
    .public-service-close{position:absolute;right:18px;top:16px;border:0;background:#f0f5f5;color:#264859;width:40px;height:40px;border-radius:50%;font-size:28px;line-height:1;cursor:pointer}
    .public-service-eyebrow{font-size:11px;font-weight:800;letter-spacing:.13em;color:#13827e;text-transform:uppercase}
    .public-service-modal-heading{padding-right:34px;margin-bottom:25px}
    .public-service-modal-heading h2{color:#183750;font-size:clamp(23px,4vw,31px);font-weight:800;margin:9px 0}
    .public-service-modal-heading p{color:#70828e;margin:0}
    .public-service-empty{margin:0;padding:20px;border:1px dashed #cbdcdb;border-radius:14px;color:#607783;text-align:center;background:#f7faf9}
    .public-service-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
    .public-service-option{width:100%;min-height:77px;display:flex;align-items:center;text-align:left;gap:13px;border:1px solid #e3ecec;background:#fff;border-radius:14px;padding:12px;cursor:pointer;transition:border-color .18s ease,transform .18s ease,box-shadow .18s ease}
    .public-service-option:hover{border-color:#41aaa3;transform:translateY(-2px);box-shadow:0 8px 24px #16656514}
    .public-service-icon{width:42px;height:42px;flex:0 0 42px;border-radius:13px;display:grid;place-items:center;background:#e8f5f2;color:#13827e;font-size:17px}
    .public-service-option small,.public-service-selected small{display:block;color:#85949b;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:3px}
    .public-service-option strong{display:block;color:#203e51;font-size:13px;line-height:1.35}
    .public-service-arrow{margin-left:auto;color:#139089;font-size:13px}
    .public-service-back{border:0;background:transparent;color:#13827e;font-weight:700;padding:0;margin:0 0 18px;cursor:pointer}
    .public-service-selected{display:flex;align-items:center;gap:13px;padding:13px;background:#f2f8f6;border:1px solid #e0eeea;border-radius:15px;margin-bottom:20px}
    .public-service-selected strong{color:#203e51;font-size:15px}
    .public-service-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .public-service-form label{display:block;color:#345060;font-size:12px;font-weight:700}
    .public-service-form label>span{font-weight:400;color:#87959c}
    .public-service-form input,.public-service-form textarea{display:block;width:100%;margin-top:6px;border:1px solid #dce6e8;border-radius:10px;padding:11px 12px;color:#213e4e;background:#fff;outline:0;font:inherit;font-size:14px}
    .public-service-form input:focus,.public-service-form textarea:focus{border-color:#1d9690;box-shadow:0 0 0 3px #1d96901c}
    .public-service-wide{grid-column:1/-1}
    .public-service-feedback{display:none;margin-top:14px;border-radius:10px;padding:12px;font-size:13px}
    .public-service-feedback.is-visible{display:block}
    .public-service-feedback.is-success{background:#e8f6ed;color:#216b41}
    .public-service-feedback.is-error{background:#fff0ef;color:#a4342c}
    .public-service-form-actions{display:flex;justify-content:flex-end;margin-top:15px}
    .public-service-submit{border:0;border-radius:11px;padding:12px 18px;background:linear-gradient(110deg,#173b58,#078b88);color:white;font-weight:700;display:flex;align-items:center;gap:11px;cursor:pointer}
    .public-service-submit:disabled{opacity:.65;cursor:wait}
    @media(max-width:600px){.public-service-grid,.public-service-form-grid{grid-template-columns:1fr}.public-service-modal{padding:25px 19px;border-radius:19px}.public-service-wide{grid-column:auto}.public-service-trigger{min-width:52px;height:54px;padding:0 14px}.public-service-trigger span{font-size:12px}.public-service-dock:hover .public-service-trigger span,.public-service-dock:focus-within .public-service-trigger span{max-width:calc(100vw - 86px)}}
    @media(prefers-reduced-motion:reduce){.public-service-trigger,.public-service-trigger span,.public-service-modal,.public-service-backdrop,.public-service-option{transition:none}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('publicServiceModal');
    if (!modal) return;

    const trigger = document.querySelector('.public-service-trigger');
    const closeButton = modal.querySelector('.public-service-close');
    const picker = modal.querySelector('.public-service-picker');
    const requestPanel = modal.querySelector('.public-service-request');
    const form = modal.querySelector('.public-service-form');
    const feedback = modal.querySelector('.public-service-feedback');
    const selectedName = modal.querySelector('.public-service-selected-name');
    let previousFocus = null;

    function openModal() {
        previousFocus = document.activeElement;
        modal.hidden = false;
        document.body.classList.add('public-service-modal-open');
        requestAnimationFrame(function () { modal.classList.add('is-open'); });
        closeButton.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        document.body.classList.remove('public-service-modal-open');
        window.setTimeout(function () { modal.hidden = true; }, 220);
        if (previousFocus) previousFocus.focus();
    }

    function showPicker() {
        picker.hidden = false;
        requestPanel.hidden = true;
        feedback.className = 'public-service-feedback';
        feedback.textContent = '';
    }

    trigger.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });
    modal.querySelector('.public-service-back').addEventListener('click', showPicker);

    modal.querySelectorAll('.public-service-option').forEach(function (button) {
        button.addEventListener('click', function () {
            form.elements.service_key.value = button.dataset.serviceKey;
            selectedName.textContent = button.dataset.serviceLabel;
            picker.hidden = true;
            requestPanel.hidden = false;
            feedback.className = 'public-service-feedback';
            feedback.textContent = '';
            form.querySelector('[name="name"]').focus();
        });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!form.reportValidity()) return;

        const submit = form.querySelector('.public-service-submit');
        const original = submit.innerHTML;
        submit.disabled = true;
        submit.innerHTML = '<span>Envoi en cours...</span><i class="fas fa-spinner fa-spin"></i>';
        feedback.className = 'public-service-feedback';
        feedback.textContent = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            });
            const result = await response.json();
            if (!response.ok) {
                const messages = result.errors ? Object.values(result.errors).flat().join(' ') : (result.message || 'Impossible d’envoyer la demande.');
                throw new Error(messages);
            }
            feedback.className = 'public-service-feedback is-visible is-success';
            feedback.textContent = result.message + ' Nous allons vous contacter pour vous écouter soigneusement.' + (result.reference ? ' Référence : ' + result.reference : '');
            if (typeof iziToast !== 'undefined') {
                iziToast.success({
                    title: 'Demande envoyée',
                    message: result.message + ' Nous allons vous contacter pour vous écouter soigneusement.' + (result.reference ? ' Référence : ' + result.reference : ''),
                    position: 'topRight',
                    timeout: 7000
                });
            }
            form.reset();
            window.setTimeout(closeModal, 3500);
        } catch (error) {
            feedback.className = 'public-service-feedback is-visible is-error';
            feedback.textContent = error.message || 'Une erreur réseau est survenue. Réessayez.';
            if (typeof iziToast !== 'undefined') {
                iziToast.error({
                    title: 'Demande non envoyée',
                    message: error.message || 'Une erreur réseau est survenue. Réessayez.',
                    position: 'topRight',
                    timeout: 7000
                });
            }
        } finally {
            submit.disabled = false;
            submit.innerHTML = original;
        }
    });
});
</script>
