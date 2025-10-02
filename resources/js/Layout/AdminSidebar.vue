<script setup>
import { computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import {
    Package,
    Users,
    UserCheck,
    LogOut,
    Building2,
    LayoutDashboard,
} from "lucide-vue-next";

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from "@/components/ui/sidebar";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { router } from "@inertiajs/vue3";

// Get admin data from page props
const { props: pageProps, url } = usePage();
const admin = computed(
    () =>
        pageProps.admin || {
            name: "Admin User",
            email: "admin@example.com",
            avatar: null,
        }
);

// Navigation items with correct URLs matching the routes
const navigationItems = [
    {
        title: "Dashboard",
        url: "/admin/dashboard",
        icon: LayoutDashboard,
        isActive: url.startsWith("/admin/dashboard"),
    },
    {
        title: "Products",
        url: "/Product",
        icon: Package,
        isActive: url.startsWith("/Product"),
    },
    {
        title: "Customers",
        url: "/Customer",
        icon: UserCheck,
        isActive: url.startsWith("/Customer"),
    },
    {
        title: "Users",
        url: "/User",
        icon: Users,
        isActive: url.startsWith("/User"),
    },
    {
        title: "Profile",
        url: "/Profile",
        icon: Building2,
        isActive: url.startsWith("/Profile"),
    },
];

// Logout function
const handleLogout = () => {
    router.post("/admin-logout");
};

// Check if URL is active
const isActiveUrl = (itemUrl) => {
    return url === itemUrl || url.startsWith(itemUrl + "/");
};
</script>
<template>
    <Sidebar class="border-r">
        <!-- Header -->
        <SidebarHeader class="p-6">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg"
                >
                    <Building2 class="h-6 w-6 text-muted-foreground" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold">SenseLix ERP</h2>
                    <p class="text-sm text-muted-foreground">Admin Panel</p>
                </div>
            </div>
        </SidebarHeader>

        <!-- Content -->
        <SidebarContent class="px-4">
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <!-- Navigation items with correct URLs -->
                        <SidebarMenuItem
                            v-for="item in navigationItems"
                            :key="item.title"
                        >
                            <SidebarMenuButton
                                :as-child="true"
                                :is-active="
                                    item.isActive || isActiveUrl(item.url)
                                "
                            >
                                <Link
                                    :href="item.url"
                                    class="flex items-center gap-3"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4"
                                    />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <!-- Footer with logout button -->
        <SidebarFooter class="p-4">
            <div class="flex items-center gap-3 rounded-lg border p-3">
                <Avatar class="h-8 w-8">
                    <AvatarImage :src="admin.avatar" />
                    <AvatarFallback class="bg-primary/10 text-primary">
                        {{ admin.name?.charAt(0) || "A" }}
                    </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ admin.name }}</p>
                    <p class="text-xs text-muted-foreground truncate">
                        {{ admin.email }}
                    </p>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="handleLogout"
                    class="h-8 w-8 p-0"
                    title="Sign Out"
                >
                    <LogOut class="h-4 w-4" />
                </Button>
            </div>
        </SidebarFooter>

        <SidebarRail />
    </Sidebar>
</template>
