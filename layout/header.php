<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Products</title>

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Tailwind Config -->
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        accent: "#9FEF00"
      },
      backdropBlur: {
        xs: '2px',
      }
    }
  }
}
</script>

<style>
/* smooth animations */
* { transition: .2s ease; }
</style>
</head>

<body class="bg-[#0f0f14] text-gray-200">
<header class="backdrop-blur-xl bg-white/5 border-b border-white/10 sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <h1 class="text-xl font-semibold text-white">My Products</h1>

    <div class="flex items-center gap-4">
      <input type="text" placeholder="Search…" class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20 text-sm placeholder-gray-400 outline-none">

      <img src="https://i.pravatar.cc/40" class="w-9 h-9 rounded-full border border-white/20">
    </div>
  </div>
</header>

<div class="flex">
