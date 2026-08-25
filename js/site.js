(function () {
  const body = document.body;
  const toggle = document.querySelector(".nav-toggle");
  const shopWrap = document.querySelector(".has-dropdown");
  const shopToggle = document.querySelector(".shop-toggle");
  const toast = document.getElementById("site-toast");

  if (toggle) {
    toggle.addEventListener("click", function () {
      body.classList.toggle("nav-open");
    });
  }

  if (shopToggle && shopWrap) {
    shopToggle.addEventListener("click", function (event) {
      event.preventDefault();
      shopWrap.classList.toggle("is-open");
    });
  }

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("is-on");
    window.setTimeout(function () {
      toast.classList.remove("is-on");
    }, 2200);
  }

  document.querySelectorAll("[data-visual-action]").forEach(function (button) {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      showToast(button.getAttribute("data-visual-action"));
    });
  });

  document.querySelectorAll(".purchase-box").forEach(function (box) {
    const priceEl = box.parentElement.querySelector("[data-price]");
    const options = box.querySelectorAll('input[name="purchase"]');
    options.forEach(function (option) {
      option.addEventListener("change", function () {
        if (priceEl) priceEl.textContent = option.value;
      });
    });
  });

  document.querySelectorAll(".faq-item button").forEach(function (button) {
    button.addEventListener("click", function () {
      const item = button.closest(".faq-item");
      const open = item.classList.contains("is-open");
      item.parentElement.querySelectorAll(".faq-item").forEach(function (other) {
        other.classList.remove("is-open");
      });
      if (!open) item.classList.add("is-open");
    });
  });

  const contactForm = document.getElementById("contact-form");
  if (contactForm) {
    contactForm.addEventListener("submit", function (event) {
      event.preventDefault();
      const note = document.getElementById("contact-note");
      if (!contactForm.checkValidity()) {
        note.textContent = "Please complete the required fields.";
        return;
      }
      contactForm.reset();
      note.textContent = "Thank you. Your message has been recorded on this page only.";
    });
  }

  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      event.preventDefault();
      showToast("Login is a visual preview only.");
    });
  }

  const newsletter = document.getElementById("newsletter-form");
  if (newsletter) {
    newsletter.addEventListener("submit", function (event) {
      event.preventDefault();
      showToast("Stay Connected is a visual preview only.");
      newsletter.reset();
    });
  }

  const storyNext = document.querySelector(".story-next");
  let featuredStory = document.querySelector(".featured-story .story-block");
  const storySlides = Array.from(document.querySelectorAll(".story-block.extra-story"));
  if (storyNext && featuredStory && storySlides.length) {
    const slides = [featuredStory].concat(storySlides).map(function (slide) {
      return {
        image: slide.querySelector("img").getAttribute("src"),
        alt: slide.querySelector("img").getAttribute("alt"),
        title: slide.querySelector("h2").textContent,
        paragraphs: Array.from(slide.querySelectorAll(".story-copy p")).map(function (p) { return p.textContent; }),
        href: slide.querySelector(".story-copy a").getAttribute("href")
      };
    });
    const carousel = featuredStory.parentElement;
    const storyTrack = document.createElement("div");
    storyTrack.className = "story-track";
    carousel.insertBefore(storyTrack, featuredStory);
    storyTrack.appendChild(featuredStory);
    let storyIndex = 0;
    let storyBusy = false;
    storyNext.addEventListener("click", function () {
      if (storyBusy) return;
      storyBusy = true;
      storyIndex = (storyIndex + 1) % slides.length;
      const slide = slides[storyIndex];
      const incoming = featuredStory.cloneNode(true);
      const image = incoming.querySelector("img");
      const paragraphs = incoming.querySelectorAll(".story-copy p");
      image.src = slide.image;
      image.alt = slide.alt;
      incoming.querySelector("h2").textContent = slide.title;
      paragraphs.forEach(function (p, index) { p.textContent = slide.paragraphs[index] || ""; });
      incoming.querySelector(".story-copy a").href = slide.href;
      storyTrack.appendChild(incoming);
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () {
          storyTrack.classList.add("is-sliding");
        });
      });
      storyTrack.addEventListener("transitionend", function finishSlide(event) {
        if (event.target !== storyTrack || event.propertyName !== "transform") return;
        storyTrack.removeEventListener("transitionend", finishSlide);
        storyTrack.classList.add("no-transition");
        featuredStory.remove();
        storyTrack.classList.remove("is-sliding");
        void storyTrack.offsetWidth;
        storyTrack.classList.remove("no-transition");
        featuredStory = incoming;
        storyBusy = false;
      });
    });
  }
})();
