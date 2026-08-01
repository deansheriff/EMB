(() => {
  "use strict";

  const qs = (selector, root = document) => root.querySelector(selector);
  const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

  const menuButton = qs("[data-menu-button]");
  const siteNav = qs("#site-nav");
  if (menuButton && siteNav) {
    menuButton.addEventListener("click", () => {
      const open = siteNav.classList.toggle("is-open");
      menuButton.setAttribute("aria-expanded", String(open));
      menuButton.setAttribute("aria-label", open ? "Close navigation" : "Open navigation");
      menuButton.textContent = open ? "×" : "☰";
      document.body.classList.toggle("menu-open", open);
    });
    qsa("a", siteNav).forEach((link) => link.addEventListener("click", () => {
      siteNav.classList.remove("is-open");
      menuButton.setAttribute("aria-expanded", "false");
      menuButton.setAttribute("aria-label", "Open navigation");
      menuButton.textContent = "☰";
      document.body.classList.remove("menu-open");
    }));
  }

  const adminButton = qs("[data-admin-menu]");
  const adminSidebar = qs("#admin-sidebar");
  if (adminButton && adminSidebar) {
    adminButton.addEventListener("click", () => {
      const open = adminSidebar.classList.toggle("is-open");
      adminButton.setAttribute("aria-expanded", String(open));
      adminButton.setAttribute("aria-label", open ? "Close admin navigation" : "Open admin navigation");
      adminButton.textContent = open ? "×" : "☰";
      document.body.classList.toggle("menu-open", open);
    });
    qsa("a", adminSidebar).forEach((link) => link.addEventListener("click", () => {
      adminSidebar.classList.remove("is-open");
      adminButton.setAttribute("aria-expanded", "false");
      adminButton.setAttribute("aria-label", "Open admin navigation");
      adminButton.textContent = "☰";
      document.body.classList.remove("menu-open");
    }));
  }

  const header = qs("[data-header]");
  if (header) {
    const updateHeader = () => header.classList.toggle("is-scrolled", window.scrollY > 8);
    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });
  }

  const hero = qs("[data-hero]");
  if (hero) {
    const slides = qsa("[data-hero-slide]", hero);
    let index = 0;
    let paused = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let timer;
    const status = qs("[data-hero-status]", hero);
    const pauseButton = qs("[data-hero-pause]", hero);
    const show = (next) => {
      index = (next + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        slide.classList.toggle("is-active", i === index);
        slide.setAttribute("aria-hidden", String(i !== index));
      });
      if (status) status.textContent = `${index + 1} / ${slides.length}`;
    };
    const resetTimer = () => {
      window.clearInterval(timer);
      if (!paused && slides.length > 1) timer = window.setInterval(() => show(index + 1), 7000);
    };
    qs("[data-hero-prev]", hero)?.addEventListener("click", () => { show(index - 1); resetTimer(); });
    qs("[data-hero-next]", hero)?.addEventListener("click", () => { show(index + 1); resetTimer(); });
    pauseButton?.addEventListener("click", () => {
      paused = !paused;
      pauseButton.textContent = paused ? "Play" : "Pause";
      pauseButton.setAttribute("aria-label", paused ? "Play carousel" : "Pause carousel");
      resetTimer();
    });
    hero.addEventListener("mouseenter", () => window.clearInterval(timer));
    hero.addEventListener("mouseleave", resetTimer);
    resetTimer();
  }

  const testimonials = qs("[data-testimonials]");
  if (testimonials) {
    const items = qsa("[data-testimonial]", testimonials);
    let current = 0;
    const show = (next) => {
      current = (next + items.length) % items.length;
      items.forEach((item, i) => {
        item.classList.toggle("is-active", i === current);
        item.setAttribute("aria-hidden", String(i !== current));
      });
    };
    qs("[data-testimonial-prev]", testimonials)?.addEventListener("click", () => show(current - 1));
    qs("[data-testimonial-next]", testimonials)?.addEventListener("click", () => show(current + 1));
  }

  qsa("[data-count]").forEach((element) => {
    const target = Number(element.dataset.count || 0);
    const run = () => {
      const started = performance.now();
      const duration = 1200;
      const tick = (now) => {
        const progress = Math.min((now - started) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = `${Math.round(target * eased).toLocaleString()}${target >= 1000 ? "+" : ""}`;
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };
    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          run();
          observer.disconnect();
        }
      }, { threshold: 0.4 });
      observer.observe(element);
    } else run();
  });

  const revealItems = qsa("[data-reveal]");
  if (revealItems.length && "IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: "0px 0px -8% 0px" });
    revealItems.forEach((item) => observer.observe(item));
  } else revealItems.forEach((item) => item.classList.add("is-visible"));

  qsa(".accordion-trigger").forEach((button) => {
    button.addEventListener("click", () => {
      const panel = button.parentElement.querySelector(".accordion-panel");
      const open = button.getAttribute("aria-expanded") === "true";
      button.setAttribute("aria-expanded", String(!open));
      button.querySelector("span").textContent = open ? "+" : "−";
      panel.hidden = open;
    });
  });

  const supportChat = qs("[data-support-chat]");
  if (supportChat) {
    const panel = qs("[data-chat-panel]", supportChat);
    const launcher = qs("[data-chat-toggle]", supportChat);
    const closeButton = qs("[data-chat-close]", supportChat);
    const externalOpeners = qsa("[data-chat-open]");
    const form = qs("[data-chat-form]", supportChat);
    const message = qs("[data-chat-message]", supportChat);
    let lastFocused = null;
    let hideTimer;

    const updateExpandedState = (open) => {
      launcher?.setAttribute("aria-expanded", String(open));
      externalOpeners.forEach((button) => button.setAttribute("aria-expanded", String(open)));
    };

    const openChat = (trigger) => {
      if (!panel) return;
      window.clearTimeout(hideTimer);
      lastFocused = trigger || document.activeElement;
      panel.hidden = false;
      updateExpandedState(true);
      window.requestAnimationFrame(() => {
        panel.classList.add("is-open");
        closeButton?.focus({ preventScroll: true });
      });
    };

    const closeChat = (restoreFocus = false) => {
      if (!panel || panel.hidden) return;
      panel.classList.remove("is-open");
      updateExpandedState(false);
      hideTimer = window.setTimeout(() => {
        if (!panel.classList.contains("is-open")) panel.hidden = true;
      }, 200);
      if (restoreFocus && lastFocused instanceof HTMLElement) lastFocused.focus({ preventScroll: true });
    };

    launcher?.addEventListener("click", () => {
      if (launcher.getAttribute("aria-expanded") === "true") closeChat(true);
      else openChat(launcher);
    });
    externalOpeners.forEach((button) => button.addEventListener("click", () => openChat(button)));
    closeButton?.addEventListener("click", () => closeChat(true));

    qsa("[data-chat-faq]", supportChat).forEach((button) => {
      button.addEventListener("click", () => {
        const answer = qs(`#${CSS.escape(button.getAttribute("aria-controls"))}`, supportChat);
        const shouldOpen = button.getAttribute("aria-expanded") !== "true";
        qsa("[data-chat-faq]", supportChat).forEach((otherButton) => {
          if (otherButton === button) return;
          otherButton.setAttribute("aria-expanded", "false");
          const otherAnswer = qs(`#${CSS.escape(otherButton.getAttribute("aria-controls"))}`, supportChat);
          if (otherAnswer) otherAnswer.hidden = true;
        });
        button.setAttribute("aria-expanded", String(shouldOpen));
        if (answer) answer.hidden = !shouldOpen;
      });
    });

    form?.addEventListener("submit", (event) => {
      const fallbackMessage = "Hello Emb Chronicles, I'd like to make an enquiry.";
      const outgoingMessage = message?.value.trim() || fallbackMessage;
      if (message && !message.value.trim()) message.value = outgoingMessage;
      try {
        const destination = new URL(supportChat.dataset.whatsappUrl || form.action, window.location.href);
        destination.searchParams.set("text", outgoingMessage);
        event.preventDefault();
        window.open(destination.toString(), "_blank", "noopener,noreferrer");
      } catch (_) {
        // Keep the form's standard GET submission as a no-JavaScript-compatible fallback.
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && panel && !panel.hidden) closeChat(true);
    });
    document.addEventListener("click", (event) => {
      if (!panel || panel.hidden || supportChat.contains(event.target) || event.target.closest("[data-chat-open]")) return;
      closeChat(false);
    });
  }

  qsa("[data-flash-close]").forEach((button) => button.addEventListener("click", () => button.closest("[data-flash]").remove()));
  qsa("[data-password-toggle]").forEach((button) => button.addEventListener("click", () => {
    const input = button.parentElement.querySelector("input");
    input.type = input.type === "password" ? "text" : "password";
    button.textContent = input.type === "password" ? "Show" : "Hide";
  }));

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-confirm]");
    if (trigger && !window.confirm(trigger.dataset.confirm)) event.preventDefault();
  });

  qsa("[data-unsaved-form]").forEach((form) => {
    let dirty = false;
    form.addEventListener("input", () => { dirty = true; });
    form.addEventListener("submit", () => { dirty = false; });
    window.addEventListener("beforeunload", (event) => {
      if (!dirty) return;
      event.preventDefault();
      event.returnValue = "";
    });
  });

  qsa("[data-rich-toolbar]").forEach((toolbar) => {
    const textarea = toolbar.parentElement.querySelector("[data-rich-text]");
    if (!textarea) return;
    qsa("[data-tag]", toolbar).forEach((button) => button.addEventListener("click", () => {
      const tag = button.dataset.tag;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const selected = textarea.value.slice(start, end) || (tag === "ul" ? "<li>List item</li>" : "Text");
      const open = `<${tag}>`;
      const close = `</${tag}>`;
      textarea.setRangeText(`${open}${selected}${close}`, start, end, "select");
      textarea.dispatchEvent(new Event("input", { bubbles: true }));
      textarea.focus();
    }));
  });

  qsa("[data-export-table]").forEach((button) => button.addEventListener("click", () => {
    const table = qs(button.dataset.exportTable);
    if (!table) return;
    const rows = qsa("tr", table).map((row) => qsa("th,td", row).map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`).join(","));
    const blob = new Blob([rows.join("\n")], { type: "text/csv;charset=utf-8" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `emb-export-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
  }));

  qsa("[data-grant-builder]").forEach((builder) => {
    const list = qs("[data-grant-field-list]", builder);
    const data = qs("[data-grant-fields-data]", builder);
    const output = qs("[data-grant-fields-json]", builder);
    if (!list || !data || !output) return;

    const createFieldRow = (field = {}) => {
      const row = document.createElement("article");
      row.className = "grant-builder-field";
      row.dataset.validation = JSON.stringify(field.validation || {});
      row.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-3">
          <strong class="text-sm text-wine" data-field-summary>Application field</strong>
          <div class="flex items-center gap-2">
            <button class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-bold text-muted hover:text-wine" type="button" data-field-up aria-label="Move field up">↑</button>
            <button class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-bold text-muted hover:text-wine" type="button" data-field-down aria-label="Move field down">↓</button>
            <button class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-red-700" type="button" data-field-remove>Remove</button>
          </div>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <div class="form-field"><label>Section title</label><input class="form-control" data-field-property="section_title" placeholder="Applicant details" required></div>
          <div class="form-field"><label>Section key</label><input class="form-control" data-field-property="section_key" placeholder="applicant"></div>
          <div class="form-field"><label>Field label</label><input class="form-control" data-field-property="label" placeholder="First name" required></div>
          <div class="form-field"><label>Field key</label><input class="form-control" data-field-property="field_key" placeholder="first_name" required></div>
          <div class="form-field"><label>Field type</label><select class="form-control" data-field-property="field_type">
            <option value="text">Short text</option><option value="email">Email</option><option value="tel">Phone</option>
            <option value="number">Number</option><option value="textarea">Long text</option><option value="select">Dropdown</option>
            <option value="radio">Multiple choice</option><option value="file">Document upload</option><option value="checkbox">Checkbox</option>
          </select></div>
          <div class="form-field"><label>Width</label><select class="form-control" data-field-property="width"><option value="full">Full width</option><option value="half">Half width</option><option value="third">One third</option></select></div>
          <div class="form-field"><label>Placeholder</label><input class="form-control" data-field-property="placeholder"></div>
          <div class="form-field"><label>Help text</label><input class="form-control" data-field-property="help_text"></div>
          <div class="form-field md:col-span-2" data-options-wrap><label>Choices</label><textarea class="form-control min-h-24" data-field-property="options" placeholder="One choice per line"></textarea><p class="field-help">Used only for dropdown and multiple-choice fields.</p></div>
          <label class="consent-row md:col-span-2"><input type="checkbox" data-field-property="is_required"><span>Required field</span></label>
        </div>`;
      ["section_title", "section_key", "label", "field_key", "field_type", "width", "placeholder", "help_text", "options"].forEach((property) => {
        const input = qs(`[data-field-property="${property}"]`, row);
        if (input) input.value = field[property] ?? (property === "width" ? "full" : property === "field_type" ? "text" : "");
      });
      qs('[data-field-property="is_required"]', row).checked = Boolean(field.is_required);
      const updateSummary = () => {
        qs("[data-field-summary]", row).textContent = qs('[data-field-property="label"]', row).value || "Application field";
      };
      row.addEventListener("input", updateSummary);
      qs("[data-field-remove]", row).addEventListener("click", () => row.remove());
      qs("[data-field-up]", row).addEventListener("click", () => {
        if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
      });
      qs("[data-field-down]", row).addEventListener("click", () => {
        if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
      });
      updateSummary();
      return row;
    };

    let initialFields = [];
    try { initialFields = JSON.parse(data.textContent || "[]"); } catch (_) {}
    initialFields.forEach((field) => list.append(createFieldRow(field)));
    if (!initialFields.length) list.append(createFieldRow({ is_required: true }));
    qs("[data-grant-add-field]", builder)?.addEventListener("click", () => {
      const previous = list.lastElementChild;
      const sectionTitle = previous ? qs('[data-field-property="section_title"]', previous)?.value : "";
      const sectionKey = previous ? qs('[data-field-property="section_key"]', previous)?.value : "";
      const row = createFieldRow({ section_title: sectionTitle, section_key: sectionKey });
      list.append(row);
      row.scrollIntoView({ behavior: "smooth", block: "center" });
    });
    builder.addEventListener("submit", () => {
      output.value = JSON.stringify(qsa(".grant-builder-field", list).map((row) => {
        const field = {};
        qsa("[data-field-property]", row).forEach((input) => {
          field[input.dataset.fieldProperty] = input.type === "checkbox" ? input.checked : input.value;
        });
        try { field.validation = JSON.parse(row.dataset.validation || "{}"); } catch (_) { field.validation = {}; }
        return field;
      }));
    });
  });

  const grantApplication = qs("[data-grant-application]");
  if (grantApplication) {
    const grantStorageKey = grantApplication.dataset.storageKey;
    if (grantStorageKey && new URLSearchParams(window.location.search).has("sent")) {
      window.localStorage.removeItem(grantStorageKey);
    }
    const welcome = qs("[data-grant-welcome]", grantApplication);
    const formView = qs("[data-grant-form-view]", grantApplication);
    const form = qs("[data-grant-form]", grantApplication);
    if (welcome && formView && form) {
      const panels = qsa("[data-grant-step]", form);
      const navButtons = qsa("[data-grant-nav]", formView);
      const stepCounts = qsa("[data-grant-step-count]", formView);
      const storageKey = grantStorageKey;
      let currentStep = 1;
      let highestStep = 1;

      const persist = () => {
        if (!storageKey) return;
        const values = {};
        new FormData(form).forEach((value, key) => {
          if (typeof value === "string" && !["_csrf", "website", "accuracy", "consent"].includes(key)) values[key] = value;
        });
        window.localStorage.setItem(storageKey, JSON.stringify({ values, currentStep, highestStep }));
      };

      const restore = () => {
        if (!storageKey) return;
        try {
          const saved = JSON.parse(window.localStorage.getItem(storageKey) || "null");
          if (!saved?.values) return;
          Object.entries(saved.values).forEach(([name, value]) => {
            qsa(`[name="${CSS.escape(name)}"]`, form).forEach((field) => {
              if (field.type === "radio" || field.type === "checkbox") field.checked = field.value === value;
              else if (field.type !== "file") field.value = value;
            });
          });
          currentStep = Math.max(1, Math.min(panels.length, Number(saved.currentStep) || 1));
          highestStep = Math.max(currentStep, Math.min(panels.length, Number(saved.highestStep) || 1));
        } catch (_) {}
      };

      const renderReview = () => {
        const review = qs("[data-grant-review]", form);
        if (!review) return;
        review.replaceChildren();
        panels.slice(0, -1).forEach((panel, index) => {
          const article = document.createElement("article");
          article.className = "rounded-[24px] border border-line bg-white p-6 shadow-soft";
          const headingRow = document.createElement("div");
          headingRow.className = "flex items-start justify-between gap-4";
          const heading = document.createElement("h3");
          heading.className = "font-display text-2xl text-wine";
          heading.textContent = qs("h2", panel)?.textContent || `Section ${index + 1}`;
          const edit = document.createElement("button");
          edit.type = "button";
          edit.className = "text-sm font-bold text-berry";
          edit.textContent = "Edit";
          edit.addEventListener("click", () => showStep(index + 1));
          headingRow.append(heading, edit);
          const list = document.createElement("dl");
          list.className = "mt-5 grid gap-4 text-sm sm:grid-cols-2";
          qsa("[data-grant-field]", panel).forEach((wrapper) => {
            const input = qs("input,select,textarea", wrapper);
            if (!input) return;
            let value = "";
            if (input.type === "radio") value = qs(`input[name="${CSS.escape(input.name)}"]:checked`, wrapper)?.value || "";
            else if (input.type === "file") value = input.files?.[0]?.name || "No file selected";
            else if (input.type === "checkbox") value = input.checked ? "Yes" : "No";
            else value = input.value;
            const item = document.createElement("div");
            const term = document.createElement("dt");
            term.className = "text-xs font-bold text-wine";
            term.textContent = wrapper.dataset.label || input.name;
            const detail = document.createElement("dd");
            detail.className = "mt-1 break-words whitespace-pre-wrap leading-6 text-muted";
            detail.textContent = value || "Not provided";
            item.append(term, detail);
            list.append(item);
          });
          article.append(headingRow, list);
          review.append(article);
        });
      };

      const showStep = (step) => {
        currentStep = Math.max(1, Math.min(panels.length, Number(step)));
        highestStep = Math.max(highestStep, currentStep);
        panels.forEach((panel, index) => panel.classList.toggle("is-active", index + 1 === currentStep));
        navButtons.forEach((button) => {
          const number = Number(button.dataset.grantNav);
          button.classList.toggle("is-active", number === currentStep);
          button.classList.toggle("is-complete", number < currentStep);
          button.disabled = number > highestStep;
        });
        stepCounts.forEach((item) => { item.textContent = `Step ${currentStep} of ${panels.length}`; });
        if (currentStep === panels.length) renderReview();
        persist();
        window.scrollTo({ top: 0, behavior: "smooth" });
      };

      const validateStep = () => {
        const panel = panels[currentStep - 1];
        const required = qsa("[required]", panel);
        for (const field of required) {
          if (!field.checkValidity()) {
            field.reportValidity();
            field.focus({ preventScroll: true });
            field.scrollIntoView({ behavior: "smooth", block: "center" });
            return false;
          }
        }
        return true;
      };

      restore();
      qs("[data-grant-start]", welcome)?.addEventListener("click", () => {
        welcome.hidden = true;
        formView.hidden = false;
        showStep(currentStep);
      });
      qsa("[data-grant-next]", form).forEach((button) => button.addEventListener("click", () => {
        if (validateStep()) showStep(currentStep + 1);
      }));
      qsa("[data-grant-back]", form).forEach((button) => button.addEventListener("click", () => showStep(currentStep - 1)));
      navButtons.forEach((button) => button.addEventListener("click", () => {
        if (!button.disabled) showStep(button.dataset.grantNav);
      }));
      qsa("[data-grant-save]", grantApplication).forEach((button) => button.addEventListener("click", () => {
        persist();
        formView.hidden = true;
        welcome.hidden = false;
        window.scrollTo({ top: 0, behavior: "smooth" });
      }));
      form.addEventListener("input", persist);
      form.addEventListener("change", persist);
      form.addEventListener("submit", (event) => {
        if (!form.reportValidity()) {
          event.preventDefault();
          return;
        }
        const submit = qs('[type="submit"]', form);
        if (submit) {
          submit.disabled = true;
          submit.textContent = "Submitting…";
        }
      });
      showStep(currentStep);
    }
  }
})();
