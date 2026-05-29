/**
 * Sa'ung Seling - Premium Interactivity and Scroll Experience
 */

document.addEventListener('DOMContentLoaded', () => {
  // Remove "noscript" class from html to activate transition effects
  document.documentElement.classList.remove('noscript');

  // Elements
  const navbar = document.getElementById('mainNavbar');
  const scrollProgressBar = document.getElementById('scroll-progress');
  const revealElements = document.querySelectorAll('.reveal-on-scroll');

  /* 1. SCROLL PROGRESS INDICATOR & STICKY NAVBAR CLASS */
  const handleScroll = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    
    // Progress calculation
    if (docHeight > 0 && scrollProgressBar) {
      const scrollPercent = (scrollTop / docHeight) * 100;
      scrollProgressBar.style.width = `${scrollPercent}%`;
    }

    // Sticky Nav background fade
    if (navbar) {
      if (scrollTop > 50) {
        navbar.classList.add('navbar-scrolled');
      } else {
        navbar.classList.remove('navbar-scrolled');
      }
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll(); // Initial check

  /* 2. INTERSECTION OBSERVER FOR EXQUISITE SCROLL REVEALS */
  if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
          // Once revealed, we don't need to observe it anymore
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px' // Trigger slightly before element enters viewport fully
    });

    revealElements.forEach(element => {
      revealObserver.observe(element);
    });
  } else {
    // Fallback if IntersectionObserver is not supported
    revealElements.forEach(element => {
      element.classList.add('active');
    });
  }

  /* 3. HORIZONTAL DRAG-TO-SCROLL FOR EXHIBITION CAROUSEL */
  const horizontalScrollers = document.querySelectorAll('.horizontal-showcase');
  
  horizontalScrollers.forEach(scroller => {
    let isDown = false;
    let startX;
    let scrollLeft;

    scroller.addEventListener('mousedown', (e) => {
      isDown = true;
      scroller.classList.add('active-dragging');
      startX = e.pageX - scroller.offsetLeft;
      scrollLeft = scroller.scrollLeft;
    });

    scroller.addEventListener('mouseleave', () => {
      isDown = false;
      scroller.classList.remove('active-dragging');
    });

    scroller.addEventListener('mouseup', () => {
      isDown = false;
      scroller.classList.remove('active-dragging');
    });

    scroller.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - scroller.offsetLeft;
      const walk = (x - startX) * 2; // scroll speed multiplier
      scroller.scrollLeft = scrollLeft - walk;
    });
  });

  /* 4. SMOOTH SCROLL ANCHORS */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({
          behavior: 'smooth'
        });
      }
    });
  });
});
