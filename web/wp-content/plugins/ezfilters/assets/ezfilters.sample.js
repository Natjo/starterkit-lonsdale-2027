export default el => {
    const form = document.querySelector('.js-ezfilters-form');
    const inputs = document.querySelectorAll('.js-ezfilters-form input');
    const result = document.querySelector('.js-ezfilters-result');
    const btns = document.querySelectorAll('.js-ezfilters-btn');

    if (!form || !result) return;

    btns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            ajax(1);
        })
    })
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        ajax(1);
    })

    const ajax = (paged = 1) => {
        document.body.classList.add('loading');

        const ajax_url = ParamsData.wp_ajax_url;
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        formData.append('action', 'ezfilters_search');
        formData.append('nonce', ezfilters_nonce);
        formData.append('paged', paged);
        formData.append('post_type', el.dataset.postType);

        xhr.open('POST', ajax_url, true);
        xhr.onreadystatechange = () => {

            if (xhr.readyState === 4 && (xhr.status === 200 || xhr.status === 0)) {
                const response = JSON.parse(xhr.response);

                result.innerHTML = response.html;
                window.history.pushState({}, '', response.url);

                initPagingLinks();
                initResetBtns();
                initResetInputBtns();
                    
                document.body.classList.remove('loading');
            }

            else {
            }
        };

        xhr.send(formData);
    }

    const initPagingLinks = () => {
        const pagingLinks = document.querySelectorAll('.js-ezfilters-paging-link');
        pagingLinks.forEach(pagingLink => {
            pagingLink.addEventListener('click', (e) => {
                e.preventDefault();
                ajax(pagingLink.dataset.index);
            })  
        })
    }

    const initResetBtns = () => {
        const btns = document.querySelectorAll('.js-ezfilters-reset-btn');
        if (!btns) return;

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                const inputs = document.querySelectorAll('.js-ezfilters-form input:checked, .js-ezfilters-form input[type="text"], js-ezfilters-form input[type="hidden"], .js-ezfilters-form select');

                inputs.forEach(input => {
                    if (input.tagName == "SELECT" || input.type == "text" || input.type == "hidden") {
                        input.value = "";
                    }

                    else {
                        input.checked = false;
                    }
                })

                ajax(1);
            })
        })
    }

    const initResetInputBtns = () => {
        const btns = document.querySelectorAll('.js-ezfilters-reset-input-btn');
        if (!btns) return;

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                btn.remove();

                if (!btn.dataset.value) {
                    var inputs = document.querySelectorAll(`input[name*="${btn.dataset.slug}"]`)

                    if (!inputs) return;

                    inputs.forEach(input => {
                        input.value = "";
                    })

                    inputs[0].dispatchEvent(new Event('change'));
                    return;
                }

                const input = document.querySelector(`input[name*="${btn.dataset.slug}"][value="${btn.dataset.value}"]`)
                if (!input) return;

                input.checked = false;
                input.dispatchEvent(new Event('change'));
            })
        })
    }

    initPagingLinks();
    initResetBtns();
    initResetInputBtns();

};