<script setup>
import {
    AudioWaveform,
    BookOpen,
    Bot,
    Command,
    Frame,
    GalleryVerticalEnd,
    Map,
    PieChart,
    Settings2,
    SquareTerminal,
    FileText,
    Receipt,
    ClipboardList,
    FileCheck,
    Users,
    Building2,
} from "lucide-vue-next";
import NavMain from "@/components/NavMain.vue";
import NavProjects from "@/components/NavProjects.vue";
import NavUser from "@/components/NavUser.vue";
import TeamSwitcher from "@/components/TeamSwitcher.vue";

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from "@/components/ui/sidebar";
import { usePage } from '@inertiajs/vue3';

// Keep defineProps for sidebar configuration
const props = defineProps({
    side: { type: String, required: false },
    variant: { type: String, required: false },
    collapsible: { type: String, required: false, default: "icon" },
    class: { type: null, required: false },
});

// Get admin data from Inertia page props
const { props: pageProps } = usePage();

// This is sample data.
const data = {
    user: {
        name: pageProps.user?.name || pageProps.admin?.name || "Guest User",
        email: pageProps.user?.email || pageProps.admin?.email || "guest@example.com",
        avatar: "https://github.com/shadcn.png", // ✅ Static avatar as requested
    },
    teams: [
        {
            name: "SenseLix ERP",
            logo: Building2,
            plan: "User Panel",
        },
    ],
    navMain: [
        {
            title: "Dashboard",
            url: "/dashboard",
            icon: SquareTerminal,
            isActive: true,
        },
        {
            title: "Quotes Management",
            url: "#",
            icon: FileText,
            items: [
                {
                    title: "Create Quotes",
                    url: "/Quote/create",
                },
                
                {
                    title: "View Quotes",
                    url: "/Quote",
                },
            ],
        },
        {
            title: "PO Customer",
            url: "#",
            icon: ClipboardList,
            items: [
                {
                    title: "Create PO",
                    url: "/po/create",
                },
                
                {
                    title: "View PO",
                    url: "/po",
                },
            ],
        },
        {
            title: "ARO",
            url: "#",
            icon: FileCheck,
            items: [
                {
                    title: "Create ARO",
                    url: "/aro/create",
                },
                
                {
                    title: "View ARO",
                    url: "/aro",
                },
            ],
        },
        {
            title: "Delivery Note",
            url: "#",
            icon: Receipt,
            items: [
                {
                    title: "Create Note",
                    url: "/delivery-notes/create",
                },
                
                {
                    title: "View Note",
                    url: "/delivery-notes",
                },
            ],
        },
        {
            title: "Invoices",
            url: "#",
            icon: BookOpen,
            items: [
                {
                    title: "Create invoice",
                    url: "/invoices/create",
                },
                
                {
                    title: "View invoices",
                    url: "/invoices",
                },
            ],
        },
      
    ],
};
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <TeamSwitcher :teams="data.teams" />
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="data.navMain" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser :user="data.user" />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>