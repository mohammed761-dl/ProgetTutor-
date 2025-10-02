<script setup>
import { ref } from "vue";
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar';

const props = defineProps({
  teams: { type: Array, required: true },
});

const { isMobile } = useSidebar();
const activeTeam = ref(props.teams[0]);
</script>

<template>
  <div class="flex justify-center items-center w-full">
    <SidebarMenu>
      <SidebarMenuItem>
        <SidebarMenuButton
          size="lg"
          class="cursor-default hover:bg-transparent"
        >
          <div
            class="flex aspect-square size-8 items-center justify-center rounded-lg bg-blue-100"
          >
            <component :is="activeTeam.logo" class="size-4 text-blue-600" />
          </div>
          <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold">
              {{ activeTeam.name }}
            </span>
            <span class="truncate text-xs text-muted-foreground">{{ activeTeam.plan }}</span>
          </div>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </div>
</template>