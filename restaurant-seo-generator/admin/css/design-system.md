# SEO Generator Design System

## Philosophy

**Inspiration:** Stripe Dashboard, Linear, Vercel, Notion
**Approach:** Clean, minimal, purposeful design with smooth interactions
**Principles:** Clarity over cleverness, consistency over novelty

---

## Color Palette

### Primary (Brand - Indigo)
- **Primary 50:** `#EEF2FF` - Backgrounds, subtle highlights
- **Primary 100:** `#E0E7FF` - Hover states
- **Primary 500:** `#6366F1` - Primary actions, links
- **Primary 600:** `#4F46E5` - Primary hover
- **Primary 700:** `#4338CA` - Primary active

**Usage:** CTAs, links, active states, brand elements

### Success (Green)
- **Success 50:** `#F0FDF4` - Background
- **Success 500:** `#10B981` - Text, icons
- **Success 600:** `#059669` - Hover

**Usage:** Completed states, success messages, positive indicators

### Warning (Amber)
- **Warning 50:** `#FFFBEB` - Background
- **Warning 500:** `#F59E0B` - Text, icons
- **Warning 600:** `#D97706` - Hover

**Usage:** In-progress states, warnings, attention needed

### Error (Red)
- **Error 50:** `#FEF2F2` - Background
- **Error 500:** `#EF4444` - Text, icons
- **Error 600:** `#DC2626` - Hover

**Usage:** Errors, destructive actions, critical alerts

### Neutrals (Slate)
- **Neutral 50:** `#F8FAFC` - App background
- **Neutral 100:** `#F1F5F9` - Card backgrounds, subtle borders
- **Neutral 200:** `#E2E8F0` - Borders, dividers
- **Neutral 300:** `#CBD5E1` - Disabled states
- **Neutral 400:** `#94A3B8` - Placeholder text
- **Neutral 500:** `#64748B` - Secondary text
- **Neutral 600:** `#475569` - Body text
- **Neutral 700:** `#334155` - Headings
- **Neutral 800:** `#1E293B` - Dark headings
- **Neutral 900:** `#0F172A` - Maximum contrast

---

## Typography

### Font Family
**Primary:** 'Outfit', system-ui, -apple-system, 'Segoe UI', sans-serif
**Monospace:** 'SF Mono', 'Monaco', 'Courier New', monospace

**Rationale:** Outfit is modern, highly readable, with excellent weights for hierarchy

### Type Scale
- **Display:** 48px / 56px (3rem / 3.5rem) - Weight 700
- **H1:** 36px / 44px (2.25rem / 2.75rem) - Weight 700
- **H2:** 30px / 38px (1.875rem / 2.375rem) - Weight 600
- **H3:** 24px / 32px (1.5rem / 2rem) - Weight 600
- **H4:** 20px / 28px (1.25rem / 1.75rem) - Weight 600
- **Body Large:** 18px / 28px (1.125rem / 1.75rem) - Weight 400
- **Body:** 16px / 24px (1rem / 1.5rem) - Weight 400
- **Body Small:** 14px / 20px (0.875rem / 1.25rem) - Weight 400
- **Caption:** 12px / 16px (0.75rem / 1rem) - Weight 500

### Font Weights
- **Regular:** 400 - Body text
- **Medium:** 500 - Labels, captions, emphasis
- **Semibold:** 600 - Subheadings, buttons
- **Bold:** 700 - Headings, strong emphasis

---

## Spacing System

**Base Unit:** 4px

```
4px   (0.25rem) - xs    - Tight padding, small gaps
8px   (0.5rem)  - sm    - Form element spacing
12px  (0.75rem) - md    - Default gap
16px  (1rem)    - base  - Default padding
20px  (1.25rem) - lg    - Section spacing
24px  (1.5rem)  - xl    - Card padding
32px  (2rem)    - 2xl   - Section padding
40px  (2.5rem)  - 3xl   - Large section spacing
48px  (3rem)    - 4xl   - Page padding
64px  (4rem)    - 5xl   - Major section breaks
```

**Rationale:** Consistent 4px base creates visual rhythm and predictability

---

## Shadows

### Elevation Levels
```css
/* Subtle - Floating elements */
box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);

/* Small - Cards, dropdowns */
box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);

/* Medium - Modals, popovers */
box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);

/* Large - Major overlays */
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

/* Extra Large - High-priority modals */
box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
```

**Rationale:** Subtle shadows create depth without distraction

---

## Border Radius

```
none:   0px     - Tables, strict layouts
sm:     4px     - Small elements, badges
base:   6px     - Buttons, inputs
md:     8px     - Cards, containers
lg:     12px    - Modals, major sections
xl:     16px    - Feature cards
full:   9999px  - Pills, avatars
```

**Rationale:** 8px base radius feels modern without being overly rounded

---

## Components

### Buttons

**Primary:**
- Background: Primary 600
- Text: White
- Hover: Primary 700
- Active: Primary 800
- Shadow: Small
- Transition: 150ms ease

**Secondary:**
- Background: Neutral 100
- Text: Neutral 700
- Border: Neutral 200
- Hover: Neutral 200

**Ghost:**
- Background: Transparent
- Text: Neutral 600
- Hover: Neutral 100

**Danger:**
- Background: Error 600
- Text: White
- Hover: Error 700

**Sizes:**
- Small: 32px height, 12px padding
- Base: 40px height, 16px padding
- Large: 48px height, 24px padding

### Cards
- Background: White
- Border: Neutral 200 (1px)
- Radius: 8px
- Padding: 24px
- Shadow: Small
- Hover: Medium shadow (interactive cards)

### Forms

**Inputs:**
- Height: 40px
- Padding: 12px 16px
- Border: Neutral 300 (1.5px)
- Radius: 6px
- Focus: Primary 500 border + ring
- Placeholder: Neutral 400

**Labels:**
- Font size: 14px
- Weight: 500
- Color: Neutral 700
- Margin bottom: 8px

**Helper Text:**
- Font size: 12px
- Color: Neutral 500
- Margin top: 4px

### Badges/Pills
- Padding: 4px 12px
- Radius: Full
- Font size: 12px
- Weight: 500
- Background: Contextual (50 tint)
- Text: Contextual (700 shade)

### Progress Bars
- Height: 8px
- Radius: Full
- Background: Neutral 200
- Fill: Primary 500 gradient
- Transition: 300ms ease

---

## Animation & Transitions

### Timing Functions
```css
/* Default - Most UI elements */
cubic-bezier(0.4, 0, 0.2, 1)

/* Ease Out - Entering elements */
cubic-bezier(0, 0, 0.2, 1)

/* Ease In - Exiting elements */
cubic-bezier(0.4, 0, 1, 1)

/* Spring - Playful interactions */
cubic-bezier(0.34, 1.56, 0.64, 1)
```

### Durations
- **Fast:** 150ms - Buttons, hovers
- **Base:** 200ms - Most transitions
- **Slow:** 300ms - Modals, complex animations
- **Slower:** 500ms - Page transitions

**Rationale:** Subtle animations feel responsive without being distracting

---

## Accessibility

### Contrast Ratios
- Body text: 4.5:1 minimum (WCAG AA)
- Large text: 3:1 minimum
- UI components: 3:1 minimum

### Focus States
- Outline: 2px solid Primary 500
- Offset: 2px
- Visible on all interactive elements

### Touch Targets
- Minimum: 44x44px (mobile)
- Desktop: 40x40px acceptable

### Motion
- Respect prefers-reduced-motion
- Disable animations when requested

---

## Layout

### Container Widths
- Mobile: 100% - 16px padding
- Tablet: 768px
- Desktop: 1200px
- Wide: 1400px

### Grid
- Columns: 12
- Gap: 24px
- Mobile: Stack to single column

### Breakpoints
```css
sm: 640px   /* Mobile landscape */
md: 768px   /* Tablet */
lg: 1024px  /* Desktop */
xl: 1280px  /* Large desktop */
2xl: 1536px /* Extra large */
```

---

## Icons

**Library:** Heroicons v2 (outline for UI, solid for badges)
**Size:** 20px default, 16px small, 24px large
**Color:** Inherit from text or contextual
**Stroke Width:** 1.5px (outline)

---

## Voice & Tone

**Empty States:** Encouraging and action-oriented
- ✅ "Let's create your first site"
- ❌ "No sites found"

**Errors:** Clear and helpful
- ✅ "API key invalid. Please check your key starts with 'sk-ant-api03-'"
- ❌ "Error 400"

**Success:** Celebratory but brief
- ✅ "✓ Site created!"
- ❌ "Success: The site has been successfully created in the database"

**Help Text:** Conversational and specific
- ✅ "We'll use this to mention your location 5-7 times per page for local SEO"
- ❌ "Enter location"

---

## Implementation Notes

1. **Load Outfit font from Google Fonts**
2. **Use CSS custom properties for colors**
3. **Mobile-first media queries**
4. **Utility-first approach where appropriate**
5. **Semantic HTML for accessibility**
6. **Progressive enhancement**
7. **Performance: minimize animations on low-end devices**

---

This design system creates a modern, professional interface that feels premium while remaining functional and accessible.
