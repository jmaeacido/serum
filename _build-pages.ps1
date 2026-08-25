$ErrorActionPreference = "Stop"
$root = "C:\laragon\www\serum72"

$head = @'
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{TITLE}}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/site.css">
</head>
<body>
  <a class="skip-link" href="#content">Skip to content</a>
  <header class="site-header">
    <a class="logo" href="index.html"><img src="assets/site/logo-footer-hi.png" alt="Native Ceuticals"></a>
    <button class="nav-toggle" type="button" aria-label="Open menu">Menu</button>
    <nav class="site-nav" aria-label="Primary">
      <a href="index.html">Home</a>
      <div class="has-dropdown">
        <button class="shop-toggle" type="button">Shop <span class="chevron" aria-hidden="true"></span></button>
        <div class="shop-menu">
          <a href="product-night-cream.html">Night Cream</a>
          <a href="product-day-cream.html">Day Cream</a>
          <a href="product-serum.html">Serum</a>
          <a href="product-mineral-sunscreen.html">Mineral Sunscreen</a>
          <a href="product-dual-action-cleanser.html">Dual Action Cleanser</a>
          <a href="shop.html">View all</a>
        </div>
      </div>
      <a href="about.html">About Us</a>
      <a href="contact.html">Contact Us</a>
    </nav>
    <div class="header-actions">
      <a class="login-link" href="login.html">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2.25c-3.6 0-8.25 1.8-8.25 5.25V21h16.5v-1.5c0-3.45-4.65-5.25-8.25-5.25Z"/></svg>
        Log In
      </a>
      <a class="cart-link" href="shop.html" aria-label="Cart">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.75 8.25V7.5a5.25 5.25 0 0 1 10.5 0v.75h2.1l1.4 12H3.25l1.4-12Zm1.5 0h7.5V7.5a3.75 3.75 0 0 0-7.5 0Z"/></svg>
      </a>
    </div>
  </header>
  <main id="content" class="page-main">
'@

$foot = @'
  </main>
  <footer class="site-footer">
    <div class="footer-grid">
      <div>
        <img class="footer-logo" src="assets/site/logo-white.png" alt="Native Ceuticals">
        <p>Smart skincare. Real result. Formulated with science and inspired by nature to elevate your everyday.</p>
      </div>
      <div>
        <h2>Company</h2>
        <a href="about.html">About Us</a>
        <a href="faqs.html">FAQs</a>
        <a href="terms.html">Terms &amp; Condition</a>
        <a href="privacy.html">Privacy Policy</a>
        <a href="shipping.html">Shipping &amp; Returns</a>
      </div>
      <div>
        <h2>Contact</h2>
        <a href="contact.html">Contact Us</a>
        <p>+1 (123) 456-7890</p>
        <p>hello@nativeceuticals.com</p>
        <p>500 S. Main Street Suite #119<br>Mooresville, NC 28115</p>
      </div>
      <div>
        <h2>Stay Connected</h2>
        <p>Join our community for skincare tips, exclusive offer and more.</p>
        <form class="newsletter" id="newsletter-form">
          <input type="email" required placeholder="Email">
          <button class="btn btn-light" type="submit">Join</button>
        </form>
        <p>Instagram · Facebook · Youtube · Tiktok</p>
      </div>
    </div>
    <div class="legal-bar">
      <span>Copyright © 2026 Native Ceuticals</span>
      <span>Powered by Alchemy Dev</span>
    </div>
  </footer>
  <div class="toast" id="site-toast" role="status"></div>
  <script src="js/site.js"></script>
</body>
</html>
'@

function Write-Page($file, $title, $body) {
  $html = ($head -replace '{{TITLE}}', $title) + "`n" + $body + "`n" + $foot
  $utf8 = New-Object System.Text.UTF8Encoding $false
  [System.IO.File]::WriteAllText((Join-Path $root $file), $html, $utf8)
}

function FaqHtml($q, $a) {
  return "    <div class=`"faq-item`"><button type=`"button`">$q</button><p>$a</p></div>"
}

$related = @'
    <section class="related-grid">
      <a class="related-card" href="product-serum.html"><img src="assets/site/p2.webp" alt=""><strong>Serum</strong><span class="price">$59.99</span></a>
      <a class="related-card" href="product-night-cream.html"><img src="assets/site/p6.webp" alt=""><strong>Night Cream</strong><span class="price">$69.99</span></a>
      <a class="related-card" href="product-day-cream.html"><img src="assets/site/p1.webp" alt=""><strong>Day Cream</strong><span class="price">$49.99</span></a>
    </section>
    <section class="mission-band">
      <div>
        <h2>We are Native Ceuticals</h2>
        <p>We believe skincare should feel simple, effective, and easy to enjoy every day. Our products are made with carefully selected ingredients that help support smoother skin, better hydration, and a brighter complexion.</p>
      </div>
      <div>
        <h2>Our Mission</h2>
        <p>"We believe skincare should feel simple and effective — every single day."</p>
      </div>
    </section>
'@

function Write-Product($file, $title, $name, $price, $sub, $img, $blurb, $ingredients, $use, $faqs) {
  $faqBlock = ($faqs | ForEach-Object { FaqHtml $_.q $_.a }) -join "`n"
  $body = @"
    <section class="product-layout">
      <div class="product-gallery"><img src="$img" alt="$name"></div>
      <div class="product-copy">
        <p class="eyebrow">Serum 72</p>
        <h1>$name</h1>
        <p class="price" data-price>$price</p>
        <p>$blurb</p>
        <form class="purchase-box" onsubmit="return false;">
          <label class="option"><span><input type="radio" name="purchase" value="$price" checked> One-time purchase</span><strong>$price</strong></label>
          <label class="option"><span><input type="radio" name="purchase" value="$sub"> Subscribe Monthly - Save 10%</span><strong>$sub</strong></label>
          <p class="qty-row"><label>Quantity <input type="number" min="1" value="1"></label></p>
          <div class="btn-row">
            <button class="btn btn-dark" type="button" data-visual-action="Added to cart (preview only).">Add to Cart</button>
            <button class="btn btn-ghost" type="button" data-visual-action="Checkout is a visual preview only.">Buy now</button>
          </div>
        </form>
      </div>
    </section>
    <section class="product-details">
      <h2>Ingredients</h2>
      <p>$ingredients</p>
      <h2>Suggested Use</h2>
      <p>$use</p>
      <p>This product is not evaluated by the FDA and is not intended to diagnose, treat, or cure any disease. Not for use by those under 18 or women who may be pregnant or nursing. Consult your physician before or during use.</p>
      <h2>What is a COA &amp; Why It Matters?</h2>
      <p>A Certificate of Analysis (COA) is a lab-tested quality report that proves what is in your product—pure, potent, and safe. Every item is backed by a COA, so you know exactly what you are getting.</p>
    </section>
    <section class="faq-list">
$faqBlock
    </section>
$related
"@
  Write-Page $file $title $body
}

Write-Product "product-night-cream.html" "Night Cream | Overnight Skin Repair &amp; Hydration | Native Ceuticals" "Night Cream" '$69.99' '$63.99' "assets/site/p6.webp" "Night Time Repair Cream is a rich, rejuvenating formula designed to support collagen production and promote smoother, more youthful-looking skin. Infused with jojoba oil, kokum butter, and mango butter, this deeply hydrating cream helps reduce the appearance of fine lines and wrinkles while nourishing the skin overnight. A blend of frankincense, myrrh, and lavender oils provides a soothing experience to complement your nightly routine." "Distilled Water, Jojoba Oil, Kokum Butter, Mango Butter, Emulsification Wax (Plant Based), Steric Acid, Frankincense (Carteri, Serrata, Frereana), Myrrh, Peppermint Oil, Sweet Orange Oil, Lavender Oil, MSM (Methylsulfonylmethane), Tocopherol E, Optiphan (Phenoxyethanol, Caprylyl Glycol), Cannabigerol (CBG Isolate)." "Apply a small amount to your face at night after cleansing. Rub in evenly for best results." @(
  @{q="What does the Night Cream do?"; a="The Night Cream deeply hydrates and nourishes the skin while you rest. It helps support smoother, younger-looking skin and reduces the appearance of fine lines and wrinkles."},
  @{q="When should I use the Night Cream?"; a="Use the Night Cream at night after cleansing. Apply a small amount evenly to your face before going to bed."},
  @{q="What are the key ingredients in the Night Cream?"; a="The Night Cream includes jojoba oil, kokum butter, mango butter, frankincense, myrrh, peppermint oil, sweet orange oil, lavender oil, MSM, Vitamin E, and CBG isolate."},
  @{q="Is the Night Cream good for dry or tired-looking skin?"; a="Yes. The Night Cream is rich and deeply hydrating, making it a strong choice for skin that feels dry, dull, tired, or in need of overnight nourishment."},
  @{q="Can I use the Night Cream with the Retinol Serum?"; a="Yes. The Retinol Serum should be applied first after cleansing. Once absorbed, apply the Night Cream to lock in moisture and support the skin overnight."}
)

Write-Product "product-day-cream.html" "Day Time Renewal Cream | Brighten &amp; Hydrate Daily | Native Ceuticals" "Day Cream" '$49.99' '$44.99' "assets/site/p1.webp" "Day Time Renewal Cream is a lightweight, nourishing formula designed to brighten dull skin, reduce the appearance of fine lines and wrinkles, and provide light SPF coverage for daily protection. Infused with red raspberry oil, carrot seed oil, and kokum butter, this hydrating cream supports a radiant complexion while helping to defend against environmental stressors." "Distilled Water, Sweet Almond Oil, Red Raspberry oil, Carrot Seed Oil, Kokum Butter, Coconut Oil, E-Wax, Steric Acid, Rosemary Oil, Peppermint Oil, Vitamin E, Phenoxyethanol, Caprylyl Glycol, Cannabigiro." "Use daily in the morning after cleansing your face. Apply evenly for brighter, protected skin." @(
  @{q="What does the Day Cream do?"; a="The Day Cream helps brighten dull-looking skin, soften the appearance of fine lines and wrinkles, and keep the skin feeling hydrated throughout the day."},
  @{q="When should I use the Day Cream?"; a="Use the Day Cream every morning after cleansing your face."},
  @{q="What are the key ingredients in the Day Cream?"; a="The Day Cream includes red raspberry oil, carrot seed oil, kokum butter, sweet almond oil, coconut oil, rosemary oil, peppermint oil, Vitamin E, and CBG."},
  @{q="Does the Day Cream provide sun protection?"; a="Yes. The Day Cream provides light SPF coverage for daily protection. For longer sun exposure, Mineral Sunscreen should still be used."},
  @{q="Who should use the Day Cream?"; a="The Day Cream is ideal for anyone who wants a lightweight daytime moisturizer that helps improve dullness, dryness, and the visible signs of aging."}
)

Write-Product "product-serum.html" "Retinol Serum | Firm, Smooth &amp; Renew Your Skin | Native Ceuticals" "Serum" '$59.99' '$53.99' "assets/site/p2.webp" "Retinol Treatment Serum is a powerful yet gentle formula designed to support a smoother, more radiant complexion. Infused with retinol, hyaluronic acid, and vitamin C, this serum helps refine skin tone, reduce the appearance of fine lines, and promote a youthful glow. Nourishing rose hip oil and sweet orange oil provide additional hydration and revitalization." "Distilled Water, Rose Hip Oil, Vitamin C, Polysorbate 80, Cetearyl Alcohol, Vitamin E T-50, Retinol, Hyaluronic Acid, Cannabigerol, Glycerin, Sweet Orange Oil, Optiphan." "Apply a few drops of serum to your face using the dropper. Gently rub in circular motions into the skin, avoiding the eye area. For optimal results, use at night after cleansing and before moisturizing." @(
  @{q="What does the Retinol Serum do?"; a="The Retinol Serum helps refine skin tone, smooth the look of fine lines, and promote a more radiant, youthful-looking complexion."},
  @{q="When should I use the Retinol Serum?"; a="Use the Retinol Serum at night after cleansing and before moisturizing."},
  @{q="How do I apply the Retinol Serum?"; a="Apply a few drops to the face using the dropper. Gently massage it into the skin using circular motions while avoiding the eye area. Once absorbed, follow with Night Cream."},
  @{q="What are the key ingredients in the Retinol Serum?"; a="The Retinol Serum includes retinol, hyaluronic acid, Vitamin C, rose hip oil, Vitamin E, glycerin, sweet orange oil, and CBG."},
  @{q="Should I wear sunscreen when using Retinol Serum?"; a="Yes. Apply Mineral Sunscreen during the day to help protect the skin, especially while using active skincare products at night."}
)

Write-Product "product-mineral-sunscreen.html" "Mineral Sunscreen | Lightweight Daily UV Protection | Native Ceuticals" "Mineral Sunscreen" '$39.99' '$35.99' "assets/site/p3.webp" "Mineral Sunscreen is a lightweight, all-natural formula designed to protect your skin from UVA and UVB rays. This sunscreen provides broad-spectrum coverage while hydrating and supporting skin health, made with non-nano zinc oxide and nourishing plant oils like raspberry seed and carrot seed oil. Reef-safe, non-toxic, and crafted with high-quality ingredients." "Distilled Water, Raspberry Oil, Carrot Seed oil, Shea Butter, Zinc Oxide, e-Wax, Steric Acid, Sandalwood EO, Coconut EO, Optiphan, Vitamin E, CBG Isolate." "Apply generously onto any skin that is directly exposed to the sun. For best results, apply 15-30 minutes before sun exposure. Repeat every 2 hours or as needed." @(
  @{q="What does the Mineral Sunscreen do?"; a="The Mineral Sunscreen protects the skin from UVA and UVB rays while helping keep the skin hydrated and supported."},
  @{q="What makes the Mineral Sunscreen different?"; a="This sunscreen uses non-nano zinc oxide and nourishing plant oils like raspberry seed oil and carrot seed oil."},
  @{q="How do I use the Mineral Sunscreen?"; a="Apply generously to any skin directly exposed to the sun. For best results, apply 15 to 30 minutes before sun exposure and reapply every 2 hours or as needed."},
  @{q="Can I use the Mineral Sunscreen every day?"; a="Yes. Daily sunscreen use is one of the most important steps in protecting the skin from sun damage, premature aging, and uneven skin tone."},
  @{q="Is the Mineral Sunscreen safe for outdoor use?"; a="Yes. It is designed for outdoor protection and is made with a reef-safe, non-toxic formula."}
)

Write-Product "product-dual-action-cleanser.html" "Dual Action Cleanser | Serum 72" "Dual Action Cleanser" '$34.99' '$31.49' "assets/site/p4.webp" "Dual Action Cleanser is a gentle yet effective foaming cleanser designed to remove dirt, oil, and impurities while supporting a refreshed, balanced complexion. Infused with lavender, rose hydrosol, and aloe vera, this botanical-rich formula cleanses without stripping the skin, leaving it feeling soft and hydrated." "Distilled Water, Lavender, Rose Hydrosol, Aloe Vera, Glycerin, Cetearyl Alcohol, E-wax, Medium Chain Triglycerides, Cocamidopropyl Betain, Vitamin E T-50, Xanthan Gum, Germell Plus, CBG Isolate." "Apply a small amount of cleanser on your face and then use gentle circular motions to lather into the skin. Rinse well with warm water." @(
  @{q="What does the Dual Action Cleanser do?"; a="The Dual Action Cleanser removes dirt, oil, and impurities while keeping the skin feeling soft, balanced, and refreshed."},
  @{q="How do I use the Dual Action Cleanser?"; a="Apply a small amount to your face, then use gentle circular motions to lather it into the skin. Rinse well with warm water."},
  @{q="Is the Dual Action Cleanser harsh on the skin?"; a="No. The formula is designed to cleanse gently while supporting the skin's natural barrier."},
  @{q="Can I use the Dual Action Cleanser daily?"; a="Yes. It can be used daily as part of your morning and evening skincare routine."},
  @{q="Who should use the Dual Action Cleanser?"; a="This cleanser is best for anyone who wants a gentle yet effective facial cleanser that removes buildup without leaving the skin feeling dry or tight."}
)

Write-Page "shop.html" "Shop | Serum 72" @'
    <section class="page-hero">
      <p class="eyebrow">Shop</p>
      <h1>The Serum 72 lineup</h1>
      <p>Five essentials, formulated with science and inspired by nature.</p>
    </section>
    <section class="shop-grid">
      <a class="product-tile" href="product-night-cream.html"><img src="assets/site/p6.webp" alt="Night Cream"><h2>Night Cream</h2><p class="price">$69.99</p></a>
      <a class="product-tile" href="product-day-cream.html"><img src="assets/site/p1.webp" alt="Day Cream"><h2>Day Cream</h2><p class="price">$49.99</p></a>
      <a class="product-tile" href="product-serum.html"><img src="assets/site/p2.webp" alt="Serum"><h2>Serum</h2><p class="price">$59.99</p></a>
      <a class="product-tile" href="product-mineral-sunscreen.html"><img src="assets/site/p3.webp" alt="Mineral Sunscreen"><h2>Mineral Sunscreen</h2><p class="price">$39.99</p></a>
      <a class="product-tile" href="product-dual-action-cleanser.html"><img src="assets/site/p4.webp" alt="Dual Action Cleanser"><h2>Dual Action Cleanser</h2><p class="price">$34.99</p></a>
    </section>
'@

Write-Page "about.html" "About Serum 72 | Clinically Inspired. Nature Approved." @'
    <section class="page-hero">
      <p class="eyebrow">About Serum 72</p>
      <h1>Smart skincare. Real results.</h1>
      <p>At Serum 72, we believe great skincare should be effective, simple, and science-backed. Formulated and manufactured by Native Ceuticals with high-quality ingredients — skincare you can trust, every day.</p>
    </section>
    <section class="about-section">
      <h2>Our mission</h2>
      <p>to create skincare that works for your skin, not against it.</p>
      <p>We are committed to creating high-performance skincare that combines science and nature in perfect balance. Every formula is thoughtfully developed to support your skin's natural processes, so you can look and feel your best every day.</p>
    </section>
    <section class="values">
      <div><h2>Science-Backed Formulas</h2><p>Advance skincare developed with proven actives.</p></div>
      <div><h2>Skin Health focused</h2><p>Our formulas support long-term skin health and resilience.</p></div>
      <div><h2>Clean and Conscious Ingredients</h2><p>We use high-quality ingredients that are safe and effective.</p></div>
      <div><h2>Simple. Effective. Essential</h2><p>Skincare made simple so you can stay consistent every day.</p></div>
    </section>
    <section class="about-section">
      <p class="eyebrow">our story</p>
      <h2>Rooted in nature. Driven by science.</h2>
      <p>Native Ceuticals was born from a belief that skincare should be effective, non-complicated, and rooted in high ingredients that works in harmony with your skin. We combine the power of native botanicals with clinically studied actives to deliver real visible results.</p>
      <p>Every product is crafted with purpose-to cleanse, treat, protect, hydrate and restore. Our approach is simple, high-quality ingredients, intentional formulas, and a commitment to your skin's long-term health.</p>
    </section>
    <section class="values">
      <div><h2>Integrity</h2><p>We do what's right — no fillers, no compromises.</p></div>
      <div><h2>Quality</h2><p>We never compromise on our standard.</p></div>
      <div><h2>Transparency</h2><p>Clear ingredients, clear communication.</p></div>
      <div><h2>Empowerment</h2><p>We empower you to feel confident on your skin.</p></div>
    </section>
'@

Write-Page "contact.html" "Contact Us | Serum 72" @'
    <section class="page-hero">
      <p class="eyebrow">Contact Native Ceuticals</p>
      <h1>We're here to help you grow.</h1>
      <p>Have a question about our product, need help with your order, or want personalized skincare advice? Our team is here for you.</p>
    </section>
    <section class="contact-grid">
      <form class="form" id="contact-form">
        <h2>We'd love to hear from you.</h2>
        <input name="first" required placeholder="First name">
        <input name="last" required placeholder="Last name">
        <input name="phone" placeholder="Phone">
        <input type="email" name="email" required placeholder="Email">
        <textarea name="message" required placeholder="Long answer"></textarea>
        <button class="btn btn-dark" type="submit">Send Message</button>
        <p class="form-note" id="contact-note"></p>
      </form>
      <div>
        <h2>Contact Information</h2>
        <p>We are committed to creating high-performance skincare that combines science and nature in perfect balance.</p>
        <p><strong>Email</strong><br>hello@nativeceuticals.com<br>We typically respond within 24 hours</p>
        <p><strong>Phone</strong><br>+1 (123) 456-7890 Monday-Friday, 9:00 am - 6:00 pm (PST)</p>
        <p><strong>Address</strong><br>500 S. Main Street Suite #119 Mooresville, NC 28115</p>
        <p><a href="faqs.html">Find quick answers in our FAQs</a></p>
      </div>
    </section>
'@

Write-Page "login.html" "Log In | Serum 72" @'
    <section class="page-hero">
      <p class="eyebrow">Account</p>
      <h1>Log In</h1>
      <p>This login screen is a visual replica only. No account is created or authenticated.</p>
    </section>
    <section class="contact-grid">
      <form class="form" id="login-form">
        <input type="email" required placeholder="Email">
        <input type="password" required placeholder="Password">
        <button class="btn btn-dark" type="submit">Log In</button>
      </form>
    </section>
'@

Write-Page "faqs.html" "FAQs | Serum 72" @'
    <section class="page-hero">
      <p class="eyebrow">Help</p>
      <h1>FAQs</h1>
      <p>Answers to common questions about our products, orders, shipping, and more.</p>
    </section>
    <section class="faq-list">
      <div class="faq-item"><button type="button">How do I start a routine?</button><p>Cleanse, treat with serum at night, moisturize, and protect with Mineral Sunscreen during the day.</p></div>
      <div class="faq-item"><button type="button">Do you offer a money-back guarantee?</button><p>Yes. The site presents a 30-day money back guarantee as shown on the homepage trust row.</p></div>
      <div class="faq-item"><button type="button">Where do you ship?</button><p>See Shipping &amp; Returns for current shipping notes. This static replica does not process orders.</p></div>
      <div class="faq-item"><button type="button">Are your products evaluated by the FDA?</button><p>These products are not evaluated by the FDA and are not intended to diagnose, treat, or cure any disease.</p></div>
      <div class="faq-item"><button type="button">What is a COA?</button><p>A Certificate of Analysis is a lab-tested quality report that shows what is in the product.</p></div>
    </section>
'@

Write-Page "terms.html" "Terms &amp; Condition | Serum 72" @'
    <section class="page-hero"><p class="eyebrow">Legal</p><h1>Terms &amp; Condition</h1></section>
    <section class="legal-copy">
      <p>By using this Serum 72 website you agree to these terms. This is a static visual replica of the public storefront and does not process payments, accounts, or live orders.</p>
      <h2>Use of the site</h2>
      <p>Content is provided for product information and brand presentation. Product names, descriptions, and pricing match the public Serum 72 / Native Ceuticals storefront as of the replica build.</p>
      <h2>Products</h2>
      <p>Skincare products are not evaluated by the FDA and are not intended to diagnose, treat, or cure any disease. Not for use by those under 18 or women who may be pregnant or nursing.</p>
      <h2>Limitation</h2>
      <p>Native Ceuticals and Serum 72 present information as-is. Consult a physician before or during use of any product.</p>
    </section>
'@

Write-Page "privacy.html" "Privacy Policy | Serum 72" @'
    <section class="page-hero"><p class="eyebrow">Legal</p><h1>Privacy Policy</h1></section>
    <section class="legal-copy">
      <p>This static site does not store form submissions on a server. Contact, login, and newsletter fields stay in the browser for this preview only.</p>
      <h2>Information we describe</h2>
      <p>The live Wix storefront may collect name, email, phone, order, and payment data. This replica does not collect that data.</p>
      <h2>Contact</h2>
      <p>Questions about privacy for the live brand can be sent to hello@nativeceuticals.com.</p>
    </section>
'@

Write-Page "shipping.html" "Shipping &amp; Returns | Serum 72" @'
    <section class="page-hero"><p class="eyebrow">Help</p><h1>Shipping &amp; Returns</h1></section>
    <section class="legal-copy">
      <h2>Shipping</h2>
      <p>The public storefront highlights fast shipping. Exact carrier times depend on the live Native Ceuticals checkout, which this replica does not include.</p>
      <h2>Returns</h2>
      <p>The homepage presents a 30-day money back guarantee. Return a product in accordance with the live store policy if you purchased there.</p>
      <h2>Contact</h2>
      <p>For order help on the live site, use Contact Us or hello@nativeceuticals.com.</p>
    </section>
'@

Write-Output "Wrote inner pages."
