import fortifyDayCreamImage from '../assets/products/fortify-day-cream.jpg';
import nightCreamImage from '../assets/products/night-cream.jpg';
import premiumCleanserImage from '../assets/products/premium-cleanser.jpg';
import radianceSerumImage from '../assets/products/radiance-serum.jpg';

export type Product = {
  slug: string;
  name: string;
  sku: string;
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
    slug: 'fortify-day-cream', name: 'Fortify Day Cream', sku: '641837796898', price: 49.99, subscriptionPrice: 44.99, image: fortifyDayCreamImage.src, storyImage: '/assets/site/carousel-fortify.png',
    short: 'Start your day with Fortify, a lightweight cream designed to help brighten the look of dullness while supporting hydration and a stronger-feeling skin barrier. Formulated with a dual peptide complex and finished with citrus and rosemary, it is crafted to help protect skin through the day ahead.',
    paragraphs: ['Daily brightening and protection in a lightweight morning cream.', 'Illuminate dullness, hydrate skin, and support a stronger-feeling skin barrier with a dual peptide complex and nourishing botanical ingredients.'],
    ingredients: 'Key Ingredients: PAL-150™ Dual Peptide Complex (Palmitoyl Tripeptide-1 + Palmitoyl Tetrapeptide-7), Vitamin E, Copaiba Essential Oil. Other Ingredients: Purified Water, Aloe Vera Juice, Jojoba Oil, Kokum Butter, Mango Butter, Plant-Based Emulsifying Wax, Stearic Acid, Carrot Seed Oil, Rosemary Extract, Optiphen (Phenoxyethanol and Caprylyl Glycol).',
    suggestedUse: 'After cleansing, apply a pea-sized amount evenly to your face, working into skin until absorbed. Approximately 120 uses.'
  },
  {
    slug: 'night-cream', name: 'Night Cream', sku: '641837796904', price: 49.99, subscriptionPrice: 44.99, image: nightCreamImage.src, storyImage: '/assets/site/carousel-night.png',
    short: 'Let Night do the work while you rest. This rich cream is designed to support your skin’s natural renewal process and help it look refreshed by morning. Featuring MSM and ectoin, along with a calming proprietary essential oil blend, it is crafted to round out your nightly routine with intention.',
    paragraphs: ['Support and repair skin as you rest with rich, restorative hydration.', 'MSM, ectoin, and a calming essential oil blend help support overnight renewal so skin looks refreshed by morning.'],
    ingredients: 'Key Ingredients: MSM (Methylsulfonylmethane), Ectoin, Proprietary Essential Oil Blend: Frankincense (Carteri, Serrata, Frereana), Myrrh, Lavender, and Sweet Orange. Other Ingredients: Purified Water, Aloe Vera Juice, Jojoba Oil, Kokum Butter, Mango Butter, Plant-Based Emulsifying Wax, Stearic Acid, Vitamin E (Tocopherol), Optiphen (Phenoxyethanol and Caprylyl Glycol).',
    suggestedUse: 'After cleansing, apply a pea-sized amount evenly to your face each evening, working into skin until absorbed. Approximately 120 uses.'
  },
  {
    slug: 'premium-cleanser', name: 'Premium Cleanser', sku: '641837796928', price: 32.99, subscriptionPrice: 29.69, image: premiumCleanserImage.src, storyImage: '/assets/site/carousel-cleanse.png',
    short: 'Refresh your routine with Cleanse, a gentle foaming formula designed to help clear away impurities and support a balanced, refreshed complexion. Featuring aloe vera and ectoin, along with calming notes of lavender and rose, it is crafted to leave skin feeling soft from the very first wash.',
    paragraphs: ['A gentle and renewing start to your routine.', 'Clear impurities away and balance your complexion with aloe vera, ectoin, and a premium dual hydrosol blend.'],
    ingredients: 'Key Ingredients: Dual Hydrosol Premium Blend, Aloe Vera Juice, Ectoin, Vitamin E. Other Ingredients: Purified Water, Glycerin, Cocamidopropyl Betaine, Medium Chain Triglycerides (MCT Oil), Plant-Based Emulsifying Wax, Cetearyl Alcohol, Xanthan Gum, Optiphen (Phenoxyethanol & Caprylyl Glycol). Contains Coconut.',
    suggestedUse: 'Apply a small amount to your face and massage in gentle circles. Rinse well with warm water.'
  },
  {
    slug: 'radiance-serum', name: 'Radiance Serum', sku: '641837796911', price: 53.99, subscriptionPrice: 48.59, image: radianceSerumImage.src, storyImage: '/assets/site/carousel-radiance.png',
    short: 'Elevate your routine with Radiance, a serum designed to help support smoother-looking texture and a more refreshed, youthful appearance over time. Featuring hyaluronic acid and retinol, finished with nourishing sweet orange oil, it is crafted to help skin look and feel revitalized.',
    paragraphs: ['Refinement and revival for smoother-looking, refreshed skin.', 'Retinol, hyaluronic acid, and vitamins C and E help refine texture, renew skin, and support a revitalized glow.'],
    ingredients: 'Key Ingredients: Retinol, Sodium Hyaluronate (Hyaluronic Acid), Vitamin C, Vitamin E. Other Ingredients: Purified Water, Rosehip Oil, Glycerin, Cetearyl Alcohol, Sweet Orange Essential Oil, Optiphen (Phenoxyethanol & Caprylyl Glycol).',
    suggestedUse: 'Apply a few drops with the dropper. Massage into face in circles, avoiding the eye area. Use at night after cleansing, before Night Cream.'
  }
];
