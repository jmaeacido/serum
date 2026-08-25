(function () {
  const stage = document.getElementById("product-stage");
  if (!stage) return;
  const products = Array.from(document.querySelectorAll(".product"));

  function resetProductAdjustments(product) {
    product.style.removeProperty("--nudge-y");
    product.style.removeProperty("--hover-scale");
    product.style.removeProperty("--card-top-offset");
  }

  function positionInfoPanel(product) {
    const card = product.querySelector(".product-card");
    const lift = product.querySelector(".product-lift");
    if (!card || !lift) return;

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        const stageRect = stage.getBoundingClientRect();
        const productRect = product.getBoundingClientRect();
        const liftRect = lift.getBoundingClientRect();
        const cardHeight = card.offsetHeight;
        const viewportPadding = 16;
        const lidOverlap = Math.min(64, liftRect.height * 0.12);
        const preferredTop = liftRect.top - cardHeight + lidOverlap;
        const isMobileLayout = window.matchMedia("(max-width: 800px)").matches;
        const mobileHintClearance = parseFloat(getComputedStyle(document.documentElement).fontSize) * 4.5;
        const minimumTop = Math.max(
          stageRect.top + viewportPadding,
          viewportPadding,
          isMobileLayout ? stageRect.top + mobileHintClearance : 0
        );
        const maximumTop = Math.max(
          minimumTop,
          Math.min(
            stageRect.bottom - cardHeight - viewportPadding,
            window.innerHeight - cardHeight - viewportPadding
          )
        );
        const vacantSpaceTop = minimumTop + Math.max(
          0,
          (liftRect.top - minimumTop - cardHeight) / 2
        );
        const desiredTop = isMobileLayout
          ? Math.min(preferredTop, vacantSpaceTop)
          : preferredTop;
        const panelTop = Math.min(Math.max(desiredTop, minimumTop), maximumTop);

        product.style.setProperty("--card-top-offset", `${panelTop - productRect.top}px`);
      });
    });
  }

  function keepInViewport(product) {
    const lift = product.querySelector(".product-lift");
    if (!lift) {
      return;
    }

    resetProductAdjustments(product);

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        const stageRect = stage.getBoundingClientRect();
        const padding = 16;
        const topBoundary = Math.max(stageRect.top + padding, padding);
        const bottomBoundary = Math.min(
          stageRect.bottom - padding,
          window.innerHeight - padding
        );
        const liftRect = lift.getBoundingClientRect();
        const transform = new DOMMatrixReadOnly(getComputedStyle(lift).transform);
        const currentScale = Math.hypot(transform.a, transform.b) || 1;
        const unscaledHeight = liftRect.height / currentScale;
        const unshiftedBottom = liftRect.bottom - transform.m42;
        const nudgeY = Math.min(0, bottomBoundary - unshiftedBottom);
        const anchoredBottom = unshiftedBottom + nudgeY;
        const availableHeight = Math.max(0, anchoredBottom - topBoundary);
        const baseScale = 1.06;
        const scaleFix = Math.min(baseScale, availableHeight / unscaledHeight);

        product.style.setProperty("--nudge-y", nudgeY + "px");

        if (scaleFix < baseScale) {
          product.style.setProperty("--hover-scale", scaleFix.toFixed(3));
        }
      });
    });
  }

  const prefersTouchNavigation = window.matchMedia("(hover: none) and (pointer: coarse)").matches;

  products.forEach((product) => {
    const trigger = product.querySelector(".product-trigger");
    const card = product.querySelector(".product-card");
    let hoverCloseTimer = null;

    const activate = () => {
      const wasActive = product.classList.contains("is-active");

      products.forEach((item) => {
        if (item === product) {
          return;
        }

        item.classList.remove("is-active");
        resetProductAdjustments(item);
      });

      product.classList.add("is-active");

      if (!wasActive) {
        keepInViewport(product);
        positionInfoPanel(product);
      }
    };

    const deactivate = () => {
      product.classList.remove("is-active");
      resetProductAdjustments(product);
    };

    const cancelScheduledDeactivate = () => {
      if (hoverCloseTimer !== null) {
        window.clearTimeout(hoverCloseTimer);
        hoverCloseTimer = null;
      }
    };

    const scheduleDeactivate = () => {
      cancelScheduledDeactivate();
      hoverCloseTimer = window.setTimeout(() => {
        hoverCloseTimer = null;
        deactivate();
      }, 160);
    };

    if (!prefersTouchNavigation) {
      [trigger, card].forEach((zone) => {
        zone.addEventListener("mouseenter", () => {
          cancelScheduledDeactivate();

          if (!product.classList.contains("is-active")) {
            activate();
          }
        });
        zone.addEventListener("mouseleave", scheduleDeactivate);
      });
    }

    product.addEventListener("focusin", activate);
    product.addEventListener("focusout", (event) => {
      if (!product.contains(event.relatedTarget)) {
        deactivate();
      }
    });

    trigger.addEventListener("click", (event) => {
      if (!prefersTouchNavigation) {
        return;
      }

      if (!product.classList.contains("is-active")) {
        event.preventDefault();
        activate();
      }
    });
  });
})();
