<script setup lang="ts">

import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminSidebar from "@/Layout/AdminSidebar.vue";
import Breadcrumb from "@/components/ui/breadcrumb/Breadcrumb.vue";
import BreadcrumbItem from "@/components/ui/breadcrumb/BreadcrumbItem.vue";
import BreadcrumbLink from "@/components/ui/breadcrumb/BreadcrumbLink.vue";
import BreadcrumbList from "@/components/ui/breadcrumb/BreadcrumbList.vue";
import BreadcrumbPage from "@/components/ui/breadcrumb/BreadcrumbPage.vue";
import BreadcrumbSeparator from "@/components/ui/breadcrumb/BreadcrumbSeparator.vue";
import Separator from "@/components/ui/separator/Separator.vue";
import SidebarInset from "@/components/ui/sidebar/SidebarInset.vue";
import SidebarProvider from "@/components/ui/sidebar/SidebarProvider.vue";
import SidebarTrigger from "@/components/ui/sidebar/SidebarTrigger.vue";

// ✅ Use Inertia's usePage to get current URL
const page = usePage();

// ✅ Admin navigation structure matching your actual routes
const adminNavMain = [
    { title: "Dashboard", url: "/admin/dashboard" },
    { title: "Products", url: "/Product" },
    { title: "Customers", url: "/Customer" },
    { title: "Users", url: "/User" },
    { title: "Admin Profile", url: "/Profile" },
];

function findAdminBreadcrumb(path: string) {
    // ✅ Check for exact matches first
    for (const item of adminNavMain) {
        if (path === item.url) {
            return [item];
        }
    }
    
    // ✅ Check for path that starts with the URL (for sub-pages)
    for (const item of adminNavMain) {
        if (path.startsWith(item.url) && item.url !== "/admin/dashboard") {
            return [item];
        }
    }
    
    // ✅ Check for serp-admin routes
    if (path.includes("/serp-admin/Product")) {
        return [{ title: "Products", url: "/Product" }];
    }
    if (path.includes("/serp-admin/Customer")) {
        return [{ title: "Customers", url: "/Customer" }];
    }
    if (path.includes("/serp-admin/User")) {
        return [{ title: "Users", url: "/User" }];
    }
    
    // ✅ Default to Dashboard
    return [{ title: "Dashboard", url: "/admin/dashboard" }];
}

// ✅ Use Inertia's current URL
const breadcrumbItems = computed(() => findAdminBreadcrumb(page.url));
</script>

<template>
    <SidebarProvider>
        <AdminSidebar />
        <SidebarInset>
            <!-- Header with breadcrumb only -->
            <header
                class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 h-4" />
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem class="hidden md:block">
                                <BreadcrumbLink href="/admin/dashboard">
                                    Admin Panel
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator class="hidden md:block" />
                            <template
                                v-for="(item, idx) in breadcrumbItems"
                                :key="item.url"
                            >
                                <BreadcrumbItem>
                                    <BreadcrumbPage>
                                        {{ item.title }}
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </template>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
            </header>

            <!-- Main content area -->
            <main class="flex flex-1 flex-col gap-4 p-4 pt-0">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>