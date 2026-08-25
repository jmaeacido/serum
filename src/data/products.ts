import nightCreamImage from '../assets/products/night-cream.png';
import dayCreamImage from '../assets/products/day-cream.png';
import serumImage from '../assets/products/serum.png';
import sunscreenImage from '../assets/products/mineral-sunscreen.png';
import cleanserImage from '../assets/products/dual-action-cleanser.webp';

export type Product = {
  slug: string;
  name: string;
  price: number;
  image: string;
  storyImage: string;
  short: string;
  paragraphs: string[];
  ingredients: string;
  subscriptionPrice: number;
  suggestedUse: string;
};

export const products: Product[] = [
  {
    slug: 'night-cream', name: 'Night Cream', price: 69.99, subscriptionPrice: 63.99, image: nightCreamImage.src, storyImage: '/assets/site/p6.webp',
    short: 'Night Time Repair Cream is a rich, rejuvenating formula designed to support collagen production and promote smoother, more youthful-looking skin. Infused with jojoba oil, kokum butter, and mango butter, this deeply hydrating cream helps reduce the appearance of fine lines and wrinkles while nourishing the skin overnight. A blend of frankincense, myrrh, and lavender oils provides a soothing experience to complement your nightly routine. Night Time Repair Cream works while you rest, revealing a revitalized complexion by morning.',
    paragraphs: ['Your skin works hard all day and deserves the care it needs to recover overnight. Without the right nourishment, it can wake up feeling dry, tired, and showing more visible signs of aging.', 'Let your skin renew while you sleep with rich, restorative hydration that helps improve texture and restore a healthy glow. Wake up to skin that feels softer, smoother, refreshed, and beautifully revitalized every morning.'],
    ingredients: 'Distilled Water, Jojoba Oil, Kokum Butter, Mango Butter, Emulsification Wax (Plant Based), Steric Acid, Frankincense, Myrrh, Peppermint Oil, Sweet Orange Oil, Lavender Oil, MSM, Tocopherol E, Optiphan, Cannabigerol (CBG Isolate).', suggestedUse: 'Apply a small amount to your face at night after cleansing. Rub in evenly for best results.'
  },
  {
    slug: 'day-cream', name: 'Day Cream', price: 49.99, subscriptionPrice: 44.99, image: dayCreamImage.src, storyImage: '/assets/site/p1.webp',
    short: 'Day Time Renewal Cream is a lightweight, nourishing formula designed to brighten dull skin, reduce the appearance of fine lines and wrinkles, and provide light SPF coverage for daily protection. Infused with red raspberry oil, carrot seed oil, and kokum butter, this hydrating cream supports a radiant complexion while helping to defend against environmental stressors. Day Time Renewal Cream is the perfect addition to your morning skincare routine.',
    paragraphs: ['Every day, your skin is exposed to harsh elements that leave it looking dry, dull, and tired.', 'Give your skin long-lasting hydration that leaves it soft, smooth and naturally radiant while supporting daily protection.'],
    ingredients: 'Distilled Water, Sweet Almond Oil, Red Raspberry Oil, Carrot Seed Oil, Kokum Butter, Coconut Oil, E-Wax, Steric Acid, Rosemary Oil, Peppermint Oil, Vitamin E, Phenoxyethanol, Caprylyl Glycol, Cannabigerol.', suggestedUse: 'Use daily in the morning after cleansing your face. Apply evenly for brighter, protected skin.'
  },
  {
    slug: 'serum', name: 'Serum', price: 59.99, subscriptionPrice: 53.99, image: serumImage.src, storyImage: '/assets/site/p2.webp',
    short: 'Retinol Treatment Serum is a powerful yet gentle formula designed to support a smoother, more radiant complexion. Infused with retinol, hyaluronic acid, and vitamin C, this serum helps refine skin tone, reduce the appearance of fine lines, and promote a youthful glow. Nourishing rose hip oil and sweet orange oil provide additional hydration and revitalization, making Retinol Treatment Serum a perfect addition to your skincare routine.',
    paragraphs: ['Fine lines, uneven texture and stubborn blemishes can make skin look older and less vibrant.', 'Support natural skin renewal with retinol, peptides and hyaluronic acid for smoother texture and a renewed glow.'],
    ingredients: 'Distilled Water, Rose Hip Oil, Vitamin C, Polysorbate 80, Cetearyl Alcohol, Vitamin E T-50, Retinol, Hyaluronic Acid, Cannabigerol, Glycerin, Sweet Orange Oil and Optiphan.', suggestedUse: 'Apply a few drops to the face at night after cleansing. Massage gently until absorbed, avoiding the eye area, then follow with Night Cream.'
  },
  {
    slug: 'mineral-sunscreen', name: 'Mineral Sunscreen', price: 39.99, subscriptionPrice: 35.99, image: sunscreenImage.src, storyImage: '/assets/site/p3.webp',
    short: 'A lightweight mineral SPF cream that helps protect skin from everyday UV exposure.',
    paragraphs: ['The sun’s harmful rays do not take a day off, and neither should your skin’s protection.', 'Protect your skin with comfortable mineral coverage designed for healthy-looking, radiant skin every day.'],
    ingredients: 'Non-nano zinc oxide and skin-comforting botanical ingredients.', suggestedUse: 'Apply evenly to the face and exposed skin before sun exposure. Reapply as needed throughout the day.'
  },
  {
    slug: 'dual-action-cleanser', name: 'Dual Action Cleanser', price: 34.99, subscriptionPrice: 31.49, image: cleanserImage.src, storyImage: '/assets/site/p4.webp',
    short: 'Dual Action Cleanser is a gentle yet effective foaming cleanser designed to remove dirt, oil, and impurities while supporting a refreshed, balanced complexion. Infused with lavender, rose hydrosol, and aloe vera, this botanical-rich formula cleanses without stripping the skin, leaving it feeling soft and hydrated. Made with high-quality, non-toxic ingredients, Dual Action Cleanser provides a pure and nourishing start to your skincare routine.',
    paragraphs: ['Dirt, excess oil and impurities collect on skin every day, leaving it feeling heavy and clogged.', 'Refresh your complexion with a deep yet gentle cleanse that removes buildup while preserving essential moisture.'],
    ingredients: 'Distilled Water, Lavender, Rose Hydrosol, Aloe Vera, Glycerin, Cetearyl Alcohol, E-wax, Medium Chain Triglycerides, Cocamidopropyl Betaine, Vitamin E T-50, Xanthan Gum, Germall Plus, CBG Isolate.', suggestedUse: 'Apply a small amount to your face and use gentle circular motions to lather into the skin. Rinse well with warm water.'
  }
];
