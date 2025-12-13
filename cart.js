(function () {
  var CART_KEY = 'foodframe_cart_v1';
  var cart = loadCart();

  function loadCart() {
    try {
      var stored = localStorage.getItem(CART_KEY);
      return stored ? JSON.parse(stored) : [];
    } catch (err) {
      console.warn('Cart load failed:', err);
      return [];
    }
  }

  function saveCart() {
    try {
      localStorage.setItem(CART_KEY, JSON.stringify(cart));
    } catch (err) {
      console.warn('Cart save failed:', err);
    }
  }

  function extractPriceValue(text) {
    var match = (text || '').match(/[0-9]+(?:\.[0-9]+)?/g);
    return match && match.length ? Number(match[match.length - 1]) : 0;
  }

  function showToast(message) {
    var toast = document.getElementById('mahmud-cart-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'mahmud-cart-toast';
      toast.style.position = 'fixed';
      toast.style.bottom = '20px';
      toast.style.right = '20px';
      toast.style.background = 'rgba(0, 0, 0, 0.8)';
      toast.style.color = '#fff';
      toast.style.padding = '12px 16px';
      toast.style.borderRadius = '12px';
      toast.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.35)';
      toast.style.fontSize = '14px';
      toast.style.zIndex = '2000';
      toast.style.transition = 'opacity 0.3s ease';
      toast.style.opacity = '0';
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.opacity = '1';
    clearTimeout(toast._hideTimer);
    toast._hideTimer = setTimeout(function () {
      toast.style.opacity = '0';
    }, 1600);
  }

  // Update cart count badge in navbar
  function updateCartCount() {
    var countEl = document.getElementById('cart-count');
    if (countEl) {
      countEl.textContent = cart.length;
    }
  }

  // Calculate total price
  function calculateTotal() {
    return cart.reduce(function(sum, item) {
      return sum + (item.price || 0);
    }, 0);
  }

  // Render cart items in dropdown
  function renderCartDropdown() {
    var itemsContainer = document.getElementById('cart-items');
    var footer = document.getElementById('cart-footer');
    var totalEl = document.getElementById('cart-total-price');

    if (!itemsContainer) return;

    if (cart.length === 0) {
      itemsContainer.innerHTML = '<p class="cart-empty">Your cart is empty</p>';
      if (footer) footer.classList.remove('show');
      return;
    }

    var html = '';
    cart.forEach(function(item, index) {
      html += '<div class="cart-item" data-index="' + index + '">';
      html += '<img class="cart-item-img" src="' + (item.image || '') + '" alt="' + item.title + '">';
      html += '<div class="cart-item-info">';
      html += '<div class="cart-item-title">' + item.title + '</div>';
      html += '<div class="cart-item-price">' + item.priceText + '</div>';
      html += '</div>';
      html += '<button class="cart-item-remove" data-index="' + index + '" type="button" aria-label="Remove item">&times;</button>';
      html += '</div>';
    });

    itemsContainer.innerHTML = html;

    // Update total
    if (totalEl) {
      totalEl.textContent = '৳' + calculateTotal();
    }

    // Show footer
    if (footer) footer.classList.add('show');

    // Bind remove buttons
    var removeButtons = itemsContainer.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var idx = parseInt(btn.getAttribute('data-index'), 10);
        removeItem(idx);
      });
    });
  }

  // Remove item from cart
  function removeItem(index) {
    if (index >= 0 && index < cart.length) {
      var removed = cart.splice(index, 1)[0];
      saveCart();
      updateCartCount();
      renderCartDropdown();
      showToast(removed.title + ' removed from cart.');
    }
  }

  // Clear all cart items
  function clearCart() {
    cart = [];
    saveCart();
    updateCartCount();
    renderCartDropdown();
    showToast('Cart cleared.');
  }

  // Toggle cart dropdown
  function toggleCartDropdown() {
    var dropdown = document.getElementById('cart-dropdown');
    if (dropdown) {
      dropdown.classList.toggle('open');
      if (dropdown.classList.contains('open')) {
        renderCartDropdown();
      }
    }
  }

  // Close cart dropdown
  function closeCartDropdown() {
    var dropdown = document.getElementById('cart-dropdown');
    if (dropdown) {
      dropdown.classList.remove('open');
    }
  }

  function addItem(card) {
    if (!card) return;

    var titleEl = card.querySelector('h3');
    var priceEl = card.querySelector('.mahmud-price');
    var imgEl = card.querySelector('img');

    var title = titleEl ? titleEl.textContent.trim() : 'Item';
    var priceText = priceEl ? priceEl.textContent.trim() : '';
    var priceValue = extractPriceValue(priceText);
    var image = imgEl ? imgEl.src : '';

    cart.push({
      title: title,
      priceText: priceText,
      price: priceValue,
      image: image,
      addedAt: Date.now()
    });

    saveCart();
    updateCartCount();
    showToast(title + ' added to cart. Total: ' + cart.length);
  }

  function bindButtons() {
    // Bind "Add to Cart" buttons
    var buttons = document.querySelectorAll('.mahmud-cart-btn');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        addItem(btn.closest('.mahmud-card'));
      });
    });

    // Bind cart toggle button
    var cartToggle = document.getElementById('cart-toggle');
    if (cartToggle) {
      cartToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleCartDropdown();
      });
    }

    // Bind cart close button
    var cartClose = document.getElementById('cart-close');
    if (cartClose) {
      cartClose.addEventListener('click', closeCartDropdown);
    }

    // Bind clear cart button
    var cartClear = document.getElementById('cart-clear');
    if (cartClear) {
      cartClear.addEventListener('click', clearCart);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      var dropdown = document.getElementById('cart-dropdown');
      var toggle = document.getElementById('cart-toggle');
      if (dropdown && toggle && 
          !dropdown.contains(e.target) && 
          !toggle.contains(e.target)) {
        closeCartDropdown();
      }
    });

    // Update cart count on load
    updateCartCount();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindButtons);
  } else {
    bindButtons();
  }

  window.foodFrameCart = {
    items: function () { return cart.slice(); },
    count: function () { return cart.length; },
    clear: clearCart,
    removeItem: removeItem,
    updateUI: function() {
      updateCartCount();
      renderCartDropdown();
    }
  };
})();
