<?php $__env->startSection('title', 'Products List'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Products List</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Products</span>
        </nav>
    </div>
    <a href="<?php echo e(route('products.create')); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Create Product
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-gray">
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product</th>
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Price</th>
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <?php $__empty_1 = true; $__currentLoopData = $products ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-surface-container/30 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-surface-gray rounded-lg overflow-hidden flex-shrink-0 border border-outline-variant/20">
                                <img class="w-full h-full object-cover" src="<?php echo e($product->images->first()?->url ?? ''); ?>" alt="<?php echo e($product->name); ?>">
                            </div>
                            <div>
                                <p class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors"><?php echo e($product->name); ?></p>
                                <p class="text-label-sm text-on-surface-variant">SKU: <?php echo e($product->sku ?? $product->id); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-body-md text-secondary"><?php echo e($product->category->name ?? '-'); ?></td>
                    <td class="px-6 py-4 font-headline-md text-headline-md text-on-surface">Rp<?php echo e(number_format($product->price ?? 0, 2)); ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-surface-gray rounded-full overflow-hidden max-w-[80px]">
                                <div class="h-full bg-success rounded-full" style="width: <?php echo e(min(($product->stock ?? 0) / 10, 100)); ?>%;"></div>
                            </div>
                            <span class="text-label-sm font-bold"><?php echo e($product->stock ?? 0); ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-success/10 text-success rounded-full text-label-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span> <?php echo e($product->stock > 0 ? 'In Stock' : 'Out of Stock'); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-on-surface-variant">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('products.show', $product->id)); ?>" class="p-1.5 hover:text-primary transition-colors" title="View"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
                            <a href="<?php echo e(route('products.edit', $product->id)); ?>" class="p-1.5 hover:text-primary transition-colors" title="Edit"><span class="material-symbols-outlined text-[20px]">edit</span></a>
                            <button class="p-1.5 hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No products found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(isset($products) && $products->hasPages()): ?>
    <div class="px-6 py-4 border-t border-outline-variant flex items-center justify-between">
        <p class="text-label-sm text-on-surface-variant">Showing <?php echo e($products->firstItem()); ?> to <?php echo e($products->lastItem()); ?> of <?php echo e($products->total()); ?> products</p>
        <div class="flex gap-1">
            <?php echo e($products->links()); ?>

        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/pages/products/index.blade.php ENDPATH**/ ?>