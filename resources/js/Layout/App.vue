<script lang="ts">
export const description = "A sidebar that collapses to icons.";
export const iframeHeight = "800px";
export const containerClass = "w-full h-full";
</script>

<script setup lang="ts">
import { computed } from "vue";
import AppSidebar from "@/components/AppSidebar.vue";
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
import { Link } from "@inertiajs/vue3";

// Sidebar nav structure (sync with AppSidebar)
const navMain = [
    { title: "Dashboard", url: "/dashboard" },
    {
        title: "Quotes Management",
        url: "#",
        items: [
            { title: "Create Quotes", url: "/Quote/create" },
            { title: "Edit Quotes", url: "/Quote/edit" },
            { title: "View Quotes", url: "/Quote" },
        ],
    },
    {
        title: "PO Customer",
        url: "#",
        items: [
            { title: "Create PO", url: "/po/create" },
            { title: "Edit PO", url: "/po/edit" },
            { title: "View PO", url: "/po" },
        ],
    },
    {
        title: "ARO",
        url: "#",
        items: [
            { title: "Create ARO", url: "/aro/create" },
            { title: "Edit ARO", url: "/aro/edit" },
            { title: "View ARO", url: "/aro" },
        ],
    },
    {
        title: "Delivery Note",
        url: "#",
        items: [
            { title: "Create Note", url: "/note/create" },
            { title: "Edit Note", url: "/note/edit" },
            { title: "View Note", url: "/note" },
        ],
    },
    {
        title: "Invoices",
        url: "#",
        items: [
            { title: "Create Facture", url: "/invoices/create" },
            { title: "Edit Facture", url: "/invoices/edit" },
            { title: "View Facture", url: "/invoices" },
        ],
    },
    {
        title: "Document Management",
        url: "#",
        items: [{ title: "View Documents", url: "/document" }],
    },
];

function findBreadcrumb(path: string) {
    // Try to find a direct match in top-level
    for (const item of navMain) {
        if (item.url === path) {
            return [item];
        }
        if (item.items) {
            for (const sub of item.items) {
                if (sub.url === path) {
                    return [item, sub];
                }
            }
        }
    }
    // Default: Dashboard
    return [navMain[0]];
}

const currentPath = computed(() => window.location.pathname);
const breadcrumbItems = computed(() => findBreadcrumb(currentPath.value));
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <header
                class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 h-4" />
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem class="hidden md:block">
                                <Link href="/"> Senselix ERP </Link>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator class="hidden md:block" />
                            <template
                                v-for="(item, idx) in breadcrumbItems"
                                :key="item.url"
                            >
                                <BreadcrumbItem
                                    v-if="idx !== breadcrumbItems.length - 1"
                                >
                                    <BreadcrumbLink :href="item.url">{{
                                        item.title
                                    }}</BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator
                                    v-if="idx !== breadcrumbItems.length - 1"
                                />
                                <BreadcrumbItem v-else>
                                    <BreadcrumbPage>
                                        <Link :href="item.url">{{
                                            item.title
                                        }}</Link>
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </template>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
            </header>
            <!-- content -->
            <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
                <slot />
            </div>
            <!-- content -->
        </SidebarInset>
    </SidebarProvider>
</template>