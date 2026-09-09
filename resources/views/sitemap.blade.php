<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('shop.index') }}</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    <url><loc>{{ route('contact.show') }}</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    @foreach (['policies.shipping', 'policies.returns', 'policies.privacy', 'policies.terms'] as $policy)
        <url><loc>{{ route($policy) }}</loc><changefreq>yearly</changefreq><priority>0.2</priority></url>
    @endforeach
    @foreach ($products as $product)
        <url><loc>{{ route('products.show', $product) }}</loc><lastmod>{{ $product->updated_at->toAtomString() }}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @endforeach
</urlset>
