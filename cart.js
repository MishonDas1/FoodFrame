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

  function showToast(message, type) {
    // Remove existing toast
    var existingToast = document.getElementById('mahmud-cart-toast');
    if (existingToast) {
      existingToast.remove();
    }

    var toast = document.createElement('div');
    toast.id = 'mahmud-cart-toast';
    
    // Determine icon based on type
    var icon = type === 'remove' ? '🗑️' : type === 'clear' ? '🧹' : '🛒';
    var bgColor = type === 'remove' ? 'linear-gradient(135deg, #ff6b6b, #ee5a5a)' : 
                  type === 'clear' ? 'linear-gradient(135deg, #f6ad55, #dd6b20)' : 
                  'linear-gradient(135deg, #667eea, #764ba2)';
    
    // Check if mobile (smaller than 980px)
    var isMobile = window.innerWidth <= 980;
    var bottomPosition = isMobile ? '100px' : '30px';
    var rightPosition = isMobile ? '50%' : '30px';
    var transformStyle = isMobile ? 'translateX(50%) translateY(100px)' : 'translateY(100px)';
    var transformIn = isMobile ? 'translateX(50%) translateY(0)' : 'translateY(0)';
    
    toast.style.cssText = `
      position: fixed;
      bottom: ${bottomPosition};
      right: ${rightPosition};
      background: ${bgColor};
      color: #fff;
      padding: 16px 24px;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      font-size: 15px;
      font-weight: 600;
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 12px;
      transform: ${transformStyle};
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      max-width: ${isMobile ? '90%' : '350px'};
    `;
    
    toast.innerHTML = '<span style="font-size: 24px;">' + icon + '</span><span>' + message + '</span>';
    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(function() {
      toast.style.transform = transformIn;
      toast.style.opacity = '1';
    });

    // Animate out
    setTimeout(function () {
      toast.style.transform = 'translateY(20px)';
      toast.style.opacity = '0';
      setTimeout(function() {
        if (toast.parentNode) toast.remove();
      }, 400);
    }, 2500);
  }

  // Animate cart button when item added
  function animateCartButton() {
    var cartBtn = document.getElementById('cart-toggle');
    if (cartBtn) {
      cartBtn.style.transform = 'scale(1.2)';
      cartBtn.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
      setTimeout(function() {
        cartBtn.style.transform = 'scale(1)';
      }, 300);
    }
  }

  // Animate cart count badge
  function animateCartCount() {
    var countEl = document.getElementById('cart-count');
    if (countEl) {
      countEl.style.transform = 'scale(1.5)';
      countEl.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
      setTimeout(function() {
        countEl.style.transform = 'scale(1)';
      }, 300);
    }
  }

  // Flying animation when adding to cart
  function flyToCart(cardElement) {
    var cartBtn = document.getElementById('cart-toggle');
    if (!cartBtn || !cardElement) return;

    var imgEl = cardElement.querySelector('img');
    if (!imgEl) return;

    // Create flying element
    var flyingEl = document.createElement('div');
    var imgRect = imgEl.getBoundingClientRect();
    var cartRect = cartBtn.getBoundingClientRect();

    flyingEl.style.cssText = `
      position: fixed;
      z-index: 9999;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background-image: url(${imgEl.src});
      background-size: cover;
      background-position: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
      pointer-events: none;
      left: ${imgRect.left + imgRect.width/2 - 30}px;
      top: ${imgRect.top + imgRect.height/2 - 30}px;
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    document.body.appendChild(flyingEl);

    // Animate to cart
    requestAnimationFrame(function() {
      flyingEl.style.left = (cartRect.left + cartRect.width/2 - 15) + 'px';
      flyingEl.style.top = (cartRect.top + cartRect.height/2 - 15) + 'px';
      flyingEl.style.width = '30px';
      flyingEl.style.height = '30px';
      flyingEl.style.opacity = '0.5';
      flyingEl.style.transform = 'rotate(360deg)';
    });

    setTimeout(function() {
      flyingEl.remove();
      animateCartButton();
    }, 800);
  }
  
  function updateCartCount() {
    var countEl = document.getElementById('cart-count');
    if (countEl) {
      var oldCount = parseInt(countEl.textContent) || 0;
      var newCount = cart.length;
      countEl.textContent = newCount;
      
      if (newCount > oldCount) {
        animateCartCount();
      }
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
      itemsContainer.innerHTML = `
        <div class="cart-empty" style="text-align: center; padding: 40px 20px;">
          <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;">🛒</div>
          <p style="color: #a0aec0; margin: 0;">Your cart is empty</p>
          <p style="color: #718096; font-size: 13px; margin-top: 8px;">Add some delicious items!</p>
        </div>
      `;
      if (footer) footer.classList.remove('show');
      return;
    }

    var html = '';
    cart.forEach(function(item, index) {
      html += '<div class="cart-item" data-index="' + index + '" style="animation: slideInRight 0.3s ease ' + (index * 0.05) + 's backwards;">';
      html += '<img class="cart-item-img" src="' + (item.image || '') + '" alt="' + item.title + '" style="transition: transform 0.3s ease;">';
      html += '<div class="cart-item-info">';
      html += '<div class="cart-item-title">' + item.title + '</div>';
      html += '<div class="cart-item-price">' + item.priceText + '</div>';
      html += '</div>';
      html += '<button class="cart-item-remove" data-index="' + index + '" type="button" aria-label="Remove item" style="transition: all 0.3s ease;">&times;</button>';
      html += '</div>';
    });

    itemsContainer.innerHTML = html;

    // Update total with animation
    if (totalEl) {
      var newTotal = '৳' + calculateTotal().toLocaleString();
      totalEl.style.transition = 'transform 0.3s ease';
      totalEl.style.transform = 'scale(1.1)';
      totalEl.textContent = newTotal;
      setTimeout(function() {
        totalEl.style.transform = 'scale(1)';
      }, 300);
    }

    // Show footer
    if (footer) footer.classList.add('show');

    // Bind remove buttons with animation
    var removeButtons = itemsContainer.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var idx = parseInt(btn.getAttribute('data-index'), 10);
        
        // Animate item removal
        var itemEl = btn.closest('.cart-item');
        if (itemEl) {
          itemEl.style.transform = 'translateX(100%)';
          itemEl.style.opacity = '0';
          itemEl.style.transition = 'all 0.3s ease';
          setTimeout(function() {
            removeItem(idx);
          }, 300);
        } else {
          removeItem(idx);
        }
      });
    });

    // Add hover effects to cart items
    var cartItems = itemsContainer.querySelectorAll('.cart-item');
    cartItems.forEach(function(item) {
      item.addEventListener('mouseenter', function() {
        var img = item.querySelector('.cart-item-img');
        if (img) img.style.transform = 'scale(1.1)';
      });
      item.addEventListener('mouseleave', function() {
        var img = item.querySelector('.cart-item-img');
        if (img) img.style.transform = 'scale(1)';
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
      showToast(removed.title + ' removed from cart', 'remove');
    }
  }

  // Clear all cart items
  function clearCart() {
    cart = [];
    saveCart();
    updateCartCount();
    renderCartDropdown();
    showToast('Cart cleared successfully!', 'clear');
  }

  // Toggle cart dropdown with animation
  function toggleCartDropdown() {
    var dropdown = document.getElementById('cart-dropdown');
    if (dropdown) {
      if (dropdown.classList.contains('open')) {
        dropdown.style.transform = 'scale(0.95) translateY(-10px)';
        dropdown.style.opacity = '0';
        setTimeout(function() {
          dropdown.classList.remove('open');
          dropdown.style.display = 'none';
        }, 200);
      } else {
        // First render the content
        renderCartDropdown();
        // Then show the dropdown
        dropdown.style.display = 'flex';
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'scale(0.95) translateY(10px)';
        dropdown.classList.add('open');
        // Force reflow for animation
        dropdown.offsetHeight;
        requestAnimationFrame(function() {
          dropdown.style.transform = 'scale(1) translateY(0)';
          dropdown.style.opacity = '1';
        });
      }
    }
  }

  // Close cart dropdown
  function closeCartDropdown() {
    var dropdown = document.getElementById('cart-dropdown');
    if (dropdown && dropdown.classList.contains('open')) {
      dropdown.style.transform = 'scale(0.95) translateY(-10px)';
      dropdown.style.opacity = '0';
      setTimeout(function() {
        dropdown.classList.remove('open');
      }, 200);
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
    
    // Flying animation
    flyToCart(card);
    
    // Show success toast
    showToast(title + ' added to cart! 🎉', 'add');
    
    // Add pulse effect to card
    card.style.boxShadow = '0 0 30px rgba(102, 126, 234, 0.5)';
    setTimeout(function() {
      card.style.boxShadow = '';
    }, 500);
  }

  function bindButtons() {
    // Bind "Add to Cart" buttons with enhanced effects
    var buttons = document.querySelectorAll('.mahmud-cart-btn');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        // Add ripple effect
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.style.cssText = 'position: absolute; background: rgba(255,255,255,0.4); border-radius: 50%; transform: scale(0); animation: ripple 0.6s linear; pointer-events: none;';
        ripple.style.left = (e.clientX - rect.left) + 'px';
        ripple.style.top = (e.clientY - rect.top) + 'px';
        ripple.style.width = ripple.style.height = '100px';
        ripple.style.marginLeft = ripple.style.marginTop = '-50px';
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.appendChild(ripple);
        setTimeout(function() { ripple.remove(); }, 600);
        
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
      cartClear.addEventListener('click', function() {
        if (cart.length === 0) {
          showToast('Cart is already empty!', 'clear');
          return;
        }
        clearCart();
      });
    }

    // Bind checkout button
    var checkoutBtn = document.querySelector('.cart-checkout-btn');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', function() {
        if (cart.length === 0) {
          showToast('Your cart is empty! Add some items first.', 'clear');
          return;
        }
        // Add loading state
        checkoutBtn.textContent = 'Redirecting...';
        checkoutBtn.style.background = '#48bb78';
        setTimeout(function() {
          window.location.href = 'checkout.php';
        }, 500);
      });
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
