/**
 * Cart + fake checkout + Watch + history API (films.php only).
 */
(function () {
    if (window.__randomPlayCartInitialized) {
        return;
    }
    window.__randomPlayCartInitialized = true;

    const cartItemsContainer = document.querySelector('.cart-items');
    const totalPriceElement = document.getElementById('total');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (!cartItemsContainer || !totalPriceElement) {
        return;
    }

    const API = 'api/history.php';
    const TRACK = '/api/track.php';
    let total = 0;
    const purchasedFilmIds = new Set();

    function getCookie(name) {
        const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }

    function isLoggedIn() {
        return getCookie('login').length > 0;
    }

    function showCartToast(message) {
        const existing = document.getElementById('cartToast');
        if (existing) {
            existing.remove();
        }
        const el = document.createElement('div');
        el.id = 'cartToast';
        el.className = 'cart-toast';
        el.setAttribute('role', 'status');
        el.textContent = message;
        document.body.appendChild(el);
        requestAnimationFrame(function () {
            el.classList.add('cart-toast--visible');
        });
        window.setTimeout(function () {
            el.classList.remove('cart-toast--visible');
            window.setTimeout(function () {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 280);
        }, 2200);
    }

    function replaceAddWithWatch(filmId, title) {
        const btn = document.querySelector('.add-to-cart[data-film-id="' + filmId + '"]');
        if (!btn) return;
        const w = document.createElement('button');
        w.type = 'button';
        w.className = 'add-to-cart watch-film';
        w.setAttribute('data-film-id', filmId);
        w.setAttribute('data-title', title);
        w.textContent = 'Watch';
        btn.replaceWith(w);
        w.addEventListener('click', onWatchClick);
    }

    function onWatchClick() {
        if (!isLoggedIn()) {
            alert('Войдите в аккаунт');
            window.location.href = '/login.php';
            return;
        }
        const filmId = this.getAttribute('data-film-id') || '';
        const title = this.getAttribute('data-title') || '';
        fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'view', filmId: filmId, title: title }),
        })
            .then(function (r) {
                return r.json().then(function (j) {
                    return { ok: r.ok, status: r.status, body: j };
                });
            })
            .then(function (res) {
                if (res.ok && res.body && res.body.ok) {
                    alert('Просмотрено');
                    showRatingPrompt(filmId);
                } else if (res.status === 401) {
                    alert('Войдите в аккаунт');
                    window.location.href = '/login.php';
                } else if (res.status === 403) {
                    alert('Сначала оплатите фильм');
                } else {
                    alert('Ошибка: ' + (res.body && res.body.error ? res.body.error : 'unknown'));
                }
            })
            .catch(function () {
                alert('Сеть недоступна');
            });
    }

    function showRatingPrompt(filmId) {
        if (document.getElementById('ratingOverlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'ratingOverlay';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(0,0,0,0.65)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '9999';

        const box = document.createElement('div');
        box.style.background = 'rgba(0, 49, 83, 0.95)';
        box.style.border = '1px solid rgba(0, 97, 140, 0.55)';
        box.style.borderRadius = '10px';
        box.style.padding = '18px 18px 14px';
        box.style.maxWidth = '520px';
        box.style.width = 'calc(100% - 40px)';
        box.style.color = '#fff';

        const title = document.createElement('div');
        title.textContent = 'Rate the movie (1–10)';
        title.style.fontSize = '18px';
        title.style.fontWeight = '600';
        title.style.marginBottom = '12px';

        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.flexWrap = 'wrap';
        row.style.gap = '10px';

        function close() {
            overlay.remove();
        }

        function send(score) {
            fetch(TRACK, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                keepalive: true,
                body: JSON.stringify({ action: 'rating', score: score, filmId: filmId, context: 'movie_after_watch' }),
            }).catch(function () {});
        }

        for (let i = 1; i <= 10; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = String(i);
            b.style.cursor = 'pointer';
            b.style.border = '0';
            b.style.borderRadius = '8px';
            b.style.padding = '10px 14px';
            b.style.background = '#00618c';
            b.style.color = '#fff';
            b.style.fontFamily = 'inherit';
            b.style.fontWeight = '600';
            b.addEventListener('mouseenter', function () {
                b.style.background = '#b1b1b1';
            });
            b.addEventListener('mouseleave', function () {
                b.style.background = '#00618c';
            });
            b.addEventListener('click', function () {
                send(i);
                close();
                alert('Thanks for rating the movie!');
            });
            row.appendChild(b);
        }

        const hint = document.createElement('div');
        hint.textContent = 'Click outside the box to close.';
        hint.style.opacity = '0.85';
        hint.style.fontSize = '13px';
        hint.style.marginTop = '12px';

        box.appendChild(title);
        box.appendChild(row);
        box.appendChild(hint);
        overlay.appendChild(box);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });

        document.body.appendChild(overlay);
    }

    function applyPurchasedFromServer(ids) {
        if (!ids || !ids.length) return;
        ids.forEach(function (filmId) {
            const orig = document.querySelector('.add-to-cart[data-film-id="' + filmId + '"]');
            const title = orig ? orig.getAttribute('data-title') || '' : '';
            replaceAddWithWatch(filmId, title);
        });
    }

    function loadStatus() {
        if (!isLoggedIn()) return;
        fetch(API + '?action=status', { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data && data.ok && data.purchasedFilmIds) {
                    purchasedFilmIds.clear();
                    data.purchasedFilmIds.forEach(function (id) {
                        purchasedFilmIds.add(id);
                    });
                    applyPurchasedFromServer(data.purchasedFilmIds);
                }
            })
            .catch(function () {});
    }

    document.querySelectorAll('.add-to-cart').forEach(function (button) {
        if (button.dataset.cartBound === '1') return;
        button.dataset.cartBound = '1';
        button.addEventListener('click', function () {
            const title = this.dataset.title;
            const price = parseInt(this.dataset.price, 10);
            const filmId = this.dataset.filmId || '';
            if (!filmId) return;
            if (!Number.isFinite(price)) return;
            if (isLoggedIn() && purchasedFilmIds.has(filmId)) {
                alert('Этот фильм уже куплен');
                return;
            }
            if (cartItemsContainer.querySelector('div[data-film-id="' + filmId + '"]')) {
                alert('Этот фильм уже в корзине');
                return;
            }
            total += price;
            const cartItem = document.createElement('div');
            cartItem.setAttribute('data-film-id', filmId);
            cartItem.setAttribute('data-title', title);
            cartItem.setAttribute('data-price', String(price));
            cartItem.textContent = title + ': ' + price + ' rub ';
            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.classList.add('remove-from-cart');
            cartItem.appendChild(removeButton);
            cartItemsContainer.appendChild(cartItem);
            totalPriceElement.textContent = String(total);
            showCartToast('Added to cart');
            removeButton.addEventListener('click', function () {
                const p = parseInt(cartItem.getAttribute('data-price') || '0', 10);
                total -= p;
                totalPriceElement.textContent = String(total);
                cartItemsContainer.removeChild(cartItem);
            });
        });
    });

    function clearCartDom() {
        while (cartItemsContainer.firstChild) {
            cartItemsContainer.removeChild(cartItemsContainer.firstChild);
        }
        total = 0;
        totalPriceElement.textContent = '0';
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function () {
            if (!cartItemsContainer.querySelector('div[data-film-id]')) {
                showCartToast('Cart is already empty');
                return;
            }
            if (!window.confirm('Remove all items from the cart?')) {
                return;
            }
            clearCartDom();
            showCartToast('Cart cleared');
        });
    }

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function () {
            if (!isLoggedIn()) {
                alert('Войдите в аккаунт');
                window.location.href = '/login.php';
                return;
            }
            const rows = cartItemsContainer.querySelectorAll('div[data-film-id]');
            if (!rows.length) {
                alert('Корзина пуста');
                return;
            }
            const items = [];
            rows.forEach(function (row) {
                items.push({
                    filmId: row.getAttribute('data-film-id') || '',
                    title: row.getAttribute('data-title') || '',
                    price: parseInt(row.getAttribute('data-price') || '0', 10),
                });
            });
            fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'checkout', items: items }),
            })
                .then(function (r) {
                    return r.json().then(function (j) {
                        return { ok: r.ok, status: r.status, body: j };
                    });
                })
                .then(function (res) {
                    if (!res.ok) {
                        if (res.status === 401) {
                            alert('Войдите в аккаунт');
                            window.location.href = '/login.php';
                        } else if (res.body && res.body.error === 'nothing_new_to_buy') {
                            alert('В корзине нет новых фильмов: всё уже куплено или данные неверны.');
                        } else {
                            alert('Ошибка оплаты: ' + (res.body && res.body.error ? res.body.error : ''));
                        }
                        return;
                    }
                    alert('Оплата прошла');
                    clearCartDom();
                    if (res.body && res.body.purchasedFilmIds) {
                        res.body.purchasedFilmIds.forEach(function (id) {
                            purchasedFilmIds.add(id);
                        });
                        applyPurchasedFromServer(res.body.purchasedFilmIds);
                    } else {
                        items.forEach(function (it) {
                            replaceAddWithWatch(it.filmId, it.title);
                        });
                    }
                })
                .catch(function () {
                    alert('Сеть недоступна');
                });
        });
    }

    document.querySelectorAll('.watch-film').forEach(function (btn) {
        btn.addEventListener('click', onWatchClick);
    });

    loadStatus();
})();
