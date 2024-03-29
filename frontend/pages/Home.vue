<template>
    <div v-if="!loading" class="flex flex-col justify-center">
        <div class="px-60 pt-8">
            <AtomBaseTitle tag="h1" content="Recently added" class="text-center mb-8" />

            <OrganismBaseGrid :columns="5">
                <div v-for="print in prints.slice(0,5)" :key="print.id">
                    <div class="relative">
                        <svg
                            :class="calculateClass(print)"
                            :fill="calculateFill(print)"
                            class="w-6 h-6 text-gray-800 dark:text-white absolute right-5 top-1.5"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 21 19"
                            @mouseenter="print.hovered = true"
                            @mouseleave="print.hovered = false"
                            @click="changeHeartStatus(print)"
                        >
                            <path stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4C5.5-1.5-1.5 5.5 4 11l7 7 7-7c5.458-5.458-1.542-12.458-7-7Z" />
                        </svg>
                    </div>
                    <NuxtLink :to="`/prints/${print.id}`">
                        <MoleculeBaseCard>
                            <template #image>
                                <img :src="print.images[0].url" alt="" class="w-full h-48 object-cover">
                            </template>
                            <template #title>
                                <AtomBaseTitle tag="h2" :content="print.title" />
                            </template>
                            <template #content>
                                <p>{{ print.description }}</p>
                            </template>
                            <template #footer>
                                <AtomBaseButton component-type="NuxtLink" text="View print" :to="`/prints/${print.id}`" />
                            </template>
                        </MoleculeBaseCard>
                    </NuxtLink>
                </div>
            </OrganismBaseGrid>
        </div>

        <div class="w-full flex justify-around">
            <div class="w-[45%] mt-10 px-16 py-10">
                <AtomBaseTitle tag="h1" content="Most popular" class="text-center mb-8" />
                <OrganismBaseGrid :columns="3">
                    <MoleculeBaseCard v-for="print in prints.slice(0,6)" :key="print.id">
                        <template #image>
                            <img :src="print.images[0].url" alt="" class="w-full h-48 object-cover">
                        </template>
                        <template #title>
                            <AtomBaseTitle tag="h2" :content="print.title" />
                        </template>
                        <template #content>
                            <p>{{ print.description }}</p>
                        </template>
                        <template #footer>
                            <AtomBaseButton component-type="NuxtLink" text="View print" to="/register" />
                        </template>
                    </MoleculeBaseCard>
                </OrganismBaseGrid>
            </div>

            <div class="w-[45%] mt-10 px-16 py-10">
                <AtomBaseTitle tag="h1" content="Recently viewed" class="text-center mb-8" />
                <OrganismBaseGrid :columns="3">
                    <MoleculeBaseCard v-for="print in prints.slice(0,6)" :key="print.id">
                        <template #image>
                            <img :src="print.images[0].url" alt="" class="w-full h-48 object-cover">
                        </template>
                        <template #title>
                            <AtomBaseTitle tag="h2" :content="print.title" />
                        </template>
                        <template #content>
                            <p>{{ print.description }}</p>
                        </template>
                        <template #footer>
                            <AtomBaseButton component-type="NuxtLink" text="View print" to="/register" />
                        </template>
                    </MoleculeBaseCard>
                </OrganismBaseGrid>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Ref } from "vue";
import { FetchError } from "ofetch";
import { $Fetch } from "nitropack";
import { NuxtLink } from "#components";

const { removeUser, getUser } = useAuth();

const { $apiFetch } = useNuxtApp();

const prints: Ref = ref([]);
const loading = ref(true);
const red = "rgb(204,0,0)";

definePageMeta({
    middleware: ["auth"]
});

interface IImage {
    id: string,
    printed_design_id: string,
    url: string,
    is_cover_image: boolean,
    user_id ?: number
}

interface IResponseBody {
    id: string, // todo check whether string or number
    title: string
    description: string,
    user_id: number,
    is_favourite: boolean,
    images: IImage
}

interface IPrint extends IResponseBody {
    hovered: boolean
}

interface IResponse {
    data: IResponseBody[]
}

onMounted(async () => {
    const response = await retrievePrints();
    if (response) {
        setPrints(response);
    } else {
        console.log("request failed");
    }
    // TODO limit to 5 and order correctly
});

async function retrievePrints (): Promise<IResponse | undefined> {
    // https://github.com/nuxt/nuxt/issues/18570
    try {
        return await ($apiFetch as $Fetch)("/api/prints", {
            headers: {
                Accept: "application/json"
            },
            method: "GET"
        });
    } catch (error: unknown) { // TODO could this be middleware?
        console.log(error);
        if (error instanceof FetchError && error.response?.status === 401) {
            removeUser();
        } else {
            console.log(error);
        }
    }
}

async function addToFavourites (print: IResponseBody) {
    try {
        await storeFavourite(print);
        const response = await retrievePrints();
        if (response) {
            setPrints(response);
        }
    } catch (error) {
        console.log(error);
    }
}

async function removeFromFavourites (print: IResponseBody) {
    try {
        await removeFavourite(print);
        const response = await retrievePrints();
        if (response) {
            setPrints(response);
        }
    } catch (error) {
        console.log(error);
    }
}

async function storeFavourite (print: IResponseBody) {
    await ($apiFetch as $Fetch)(`/api/users/${getUser()?.id}/favourite-printed-designs/${print.id}`, {
        method: "PATCH"
    });
}

async function removeFavourite(print: IResponseBody) {
    await ($apiFetch as $Fetch)(`/api/users/${getUser()?.id}/favourite-printed-designs/${print.id}`, {
        method: "DELETE"
    });
}

function setPrints (response: IResponse) {
    prints.value = response.data;
    loading.value = false;
}

function calculateFill (print: IResponseBody) {
    if (print.is_favourite) {
        return red;
    }

    return "none";
}

function calculateClass (print: IPrint) {
    const suffix = (print.hovered ? " hover-effect" : "");
    if (print.is_favourite) {
        return "favourited" + suffix;
    }

    return "not-favourited" + suffix;
}

async function changeHeartStatus (print: IPrint) {
    print.hovered = false; // Remove if I want the heart effect to be immediate
    if (!print.is_favourite) {
        await addToFavourites(print);
    } else {
        await removeFromFavourites(print);
    }
}
// TODO shuffle at the top in nav
// TODO use nuxt-img
</script>

<style>
.favourited.hover-effect {
    fill: none;
}

.not-favourited.hover-effect {
    fill: v-bind(red);
}

</style>
