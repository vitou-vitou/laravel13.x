<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Customer Service Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <header class="bg-blue-700 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white text-blue-700 flex items-center justify-center font-bold text-xl shadow">
                    +
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Hospital Customer Care</h1>
                    <p class="text-xs text-blue-100">Patient Navigation, Queues & Inquiries</p>
                </div>
            </div>
            <div class="flex gap-2 text-sm">
                <a href="#triage" class="px-3 py-1.5 rounded-lg bg-blue-800 hover:bg-blue-900 transition">FAQ Triage</a>
                <a href="#departments" class="px-3 py-1.5 rounded-lg bg-blue-800 hover:bg-blue-900 transition">Departments</a>
            </div>
        </div>
    </header>

    <div class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-6xl mx-auto px-4 py-3 text-xs sm:text-sm text-amber-900 flex items-start gap-2">
            <span class="font-bold text-amber-700 uppercase tracking-wide">Notice:</span>
            <span><?php echo e($disclaimer); ?></span>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 py-8 flex-1 w-full space-y-10">
        <section class="text-center space-y-4 max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">How can we assist you today?</h2>
            <p class="text-sm text-slate-600">Quickly find visiting guidelines, test preparations, or hospital counter locations.</p>
            <div class="relative">
                <input
                    type="text"
                    id="faq-search"
                    placeholder="Search FAQs, prep instructions, or departments..."
                    class="w-full px-4 py-3 pl-11 rounded-xl border border-slate-300 shadow-sm focus:ring-2 focus:ring-blue-600 focus:outline-none text-sm bg-white"
                />
                <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </div>
        </section>

        <section id="triage" class="space-y-6">
            <div class="flex justify-between items-end border-b pb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">FAQ Decision Tree & Guidelines</h3>
                    <p class="text-xs text-slate-500">Step-by-step guidance for visitors and patients</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="faq-container">
                <?php $__empty_1 = true; $__currentLoopData = $faqTree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3 faq-category-block" data-category="<?php echo e(strtolower($category)); ?>">
                        <div class="flex items-center gap-2 border-b pb-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h4 class="font-semibold text-slate-900 text-sm tracking-wide"><?php echo e($category); ?></h4>
                        </div>
                        <div class="space-y-2">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <details class="group border border-slate-100 rounded-lg p-3 bg-slate-50/50 hover:bg-slate-50 faq-item transition" data-text="<?php echo e(strtolower($item->question . ' ' . $item->answer)); ?>">
                                    <summary class="font-medium text-xs sm:text-sm text-slate-800 cursor-pointer list-none flex justify-between items-center gap-2">
                                        <span><?php echo e($item->question); ?></span>
                                        <span class="text-blue-600 text-xs font-bold group-open:rotate-180 transition-transform">▼</span>
                                    </summary>
                                    <div class="mt-2.5 text-xs text-slate-600 leading-relaxed border-t pt-2 space-y-2">
                                        <p><?php echo e($item->answer); ?></p>
                                        <?php if($item->targetDepartment): ?>
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-[11px] font-medium">
                                                <span>Assisting Counter:</span>
                                                <span class="font-bold"><?php echo e($item->targetDepartment->name); ?> (<?php echo e($item->targetDepartment->counter_location); ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-2 p-8 text-center bg-white rounded-xl border border-slate-200 text-slate-500 text-sm">
                        No FAQ guidance items registered yet.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="departments" class="space-y-6">
            <div class="border-b pb-3">
                <h3 class="text-lg font-bold text-slate-900">Hospital Departments & Counters</h3>
                <p class="text-xs text-slate-500">Physical desks, operating hours, and extensions</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="dept-container">
                <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition space-y-2.5 dept-card" data-dept="<?php echo e(strtolower($dept->name . ' ' . $dept->code . ' ' . $dept->counter_location)); ?>">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm"><?php echo e($dept->name); ?></h4>
                                <span class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"><?php echo e($dept->code); ?></span>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1" title="Counter Open"></span>
                        </div>
                        <?php if($dept->description): ?>
                            <p class="text-xs text-slate-500 line-clamp-2"><?php echo e($dept->description); ?></p>
                        <?php endif; ?>
                        <div class="text-[11px] text-slate-600 space-y-1 border-t pt-2">
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">Location:</span>
                                <span class="font-medium text-slate-800"><?php echo e($dept->counter_location); ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">Hours:</span>
                                <span class="font-medium text-slate-800"><?php echo e($dept->operating_hours); ?></span>
                            </div>
                            <?php if($dept->phone): ?>
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400">Phone:</span>
                                    <span class="font-medium text-slate-800"><?php echo e($dept->phone); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-3 p-8 text-center bg-white rounded-xl border border-slate-200 text-slate-500 text-sm">
                        No departments currently registered.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 text-slate-400 text-xs py-6 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p>&copy; <?php echo e(date('Y')); ?> Hospital Care Network. All rights reserved.</p>
            <p class="text-slate-500">Non-Clinical Patient Service System</p>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('faq-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.faq-item').forEach(item => {
                    const match = item.getAttribute('data-text').includes(term);
                    item.style.display = match || term === '' ? 'block' : 'none';
                });
                document.querySelectorAll('.dept-card').forEach(card => {
                    const match = card.getAttribute('data-dept').includes(term);
                    card.style.display = match || term === '' ? 'block' : 'none';
                });
            });
        }
    </script>
</body>
</html>
<?php /**PATH D:\laravel13.x\examples\hospital-customer-service\resources\views/portal.blade.php ENDPATH**/ ?>