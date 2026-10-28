<div class="assistant">
    <button type="button" id="assistant-toggle" class="assistant-toggle">💬 Asistente</button>

    <div id="assistant-panel" class="assistant-panel" hidden>
        <div class="assistant-header">
            <strong>Asistente TechStore</strong>
            <button type="button" id="assistant-close" aria-label="Cerrar">×</button>
        </div>

        <div id="assistant-messages" class="assistant-messages"></div>

        <div class="assistant-chips">
            <button type="button" data-q="Audífonos baratos">Audífonos baratos</button>
            <button type="button" data-q="Mouse para gaming">Mouse gaming</button>
            <button type="button" data-q="Cargador para celular">Cargador celular</button>
        </div>

        <form id="assistant-form" class="assistant-form">
            <input type="text" id="assistant-input" maxlength="300" autocomplete="off"
                   placeholder="Ej. teclado por menos de 900">
            <button type="submit" class="btn">Enviar</button>
        </form>
    </div>
</div>

<script>
(function () {
    var toggle = document.getElementById('assistant-toggle');
    var panel = document.getElementById('assistant-panel');
    var closeBtn = document.getElementById('assistant-close');
    var messages = document.getElementById('assistant-messages');
    var form = document.getElementById('assistant-form');
    var input = document.getElementById('assistant-input');
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var url = @json(route('assistant.ask'));
    var greeted = false;

    function addMessage(text, who) {
        var div = document.createElement('div');
        div.className = 'msg ' + who;
        div.textContent = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function addProducts(list) {
        list.forEach(function (p) {
            var a = document.createElement('a');
            a.className = 'msg-product';
            a.href = p.url;

            if (p.image) {
                var img = document.createElement('img');
                img.src = p.image;
                img.alt = p.name;
                a.appendChild(img);
            }

            var box = document.createElement('div');
            var name = document.createElement('strong');
            name.textContent = p.name;
            var meta = document.createElement('span');
            meta.textContent = '$' + p.price + ' · ' + p.brand;
            box.appendChild(name);
            box.appendChild(meta);
            if (p.reason) {
                var reason = document.createElement('small');
                reason.textContent = p.reason;
                box.appendChild(reason);
            }
            a.appendChild(box);
            messages.appendChild(a);
        });
        messages.scrollTop = messages.scrollHeight;
    }

    function send(text) {
        text = text.trim();
        if (!text) { return; }
        addMessage(text, 'user');
        var loading = addMessage('Escribiendo...', 'bot');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ message: text })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            loading.textContent = data.reply || 'No pude procesar tu mensaje.';
            addProducts(data.products || []);
        })
        .catch(function () {
            loading.textContent = 'No pude conectarme con el asistente.';
        });
    }

    toggle.addEventListener('click', function () {
        panel.hidden = !panel.hidden;
        if (!panel.hidden) {
            if (!greeted) {
                addMessage('¡Hola! Dime qué buscas y te recomiendo productos del catálogo.', 'bot');
                greeted = true;
            }
            input.focus();
        }
    });
    closeBtn.addEventListener('click', function () { panel.hidden = true; });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value;
        input.value = '';
        send(text);
    });

    document.querySelectorAll('.assistant-chips button').forEach(function (b) {
        b.addEventListener('click', function () { send(b.dataset.q); });
    });
})();
</script>
