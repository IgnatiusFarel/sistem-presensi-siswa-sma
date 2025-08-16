<template>
  <div>    
    <div v-if="!selectedBerita">
      <div v-if="beritaList.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-5">
        <div
          v-for="berita in beritaList"
          :key="berita.daftar_berita_id"
          class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-white transition hover:shadow-md hover:-translate-y-1 duration-200"
        >
          <div class="relative w-full h-44">
            <img
              :src="getThumbnailUrl(berita.thumbnail)"
              :alt="berita.slug || 'Gambar Berita'"
              class="w-full h-full object-cover"
            />
            <div
              class="absolute top-2 left-2 px-3 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded-full shadow"
            >
              {{ berita.kategori }}
            </div>
          </div>

          <div class="p-4">
            <div
              class="font-bold text-lg text-gray-900 mb-1 line-clamp-2 cursor-pointer hover:underline"
              @click="lihatDetail(berita)"
            >
              {{ berita.judul }}
            </div>

            <div class="flex items-center text-sm text-gray-500 mb-2 gap-1">
              <PhCalendarBlank :size="16" />
              <span>{{ formatTanggal(berita.updated_at) }}</span>
            </div>

            <div
              class="text-sm text-gray-600 mb-4 line-clamp-2"
              v-html="berita.konten"
            ></div>

            <div class="flex justify-between items-center text-sm">
              <div class="flex items-center gap-2 text-gray-600">
                <img
                  src="https://ui-avatars.com/api/?name=Admin&background=random"
                  class="w-6 h-6 rounded-full"
                  :alt="berita.user?.name || 'Admin'"
                />
                {{ berita.dibuat_oleh }}
              </div>

              <a
                href="#"
                @click.prevent="lihatDetail(berita)"
                class="text-blue-600 font-medium hover:underline flex items-center gap-1"
              >
                Baca Selengkapnya
                <PhCaretRight :size="16" />
              </a>
            </div>
          </div>
        </div>
      </div>
            
      <div v-else class="flex flex-col items-center justify-center py-20 px-5">
        <div class="text-center max-w-md">      
          <div class="mb-6">
            <svg class="w-24 h-24 text-gray-300 mx-auto" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"></path>
              <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V9a1 1 0 00-1-1h-1v3a2 2 0 01-2 2H4.5a1.5 1.5 0 010-3H11V7z"></path>
            </svg>
          </div>
                    
          <h3 class="text-xl font-semibold text-gray-800 mb-2">
            Belum Ada Berita
          </h3>
          <p class="text-gray-500 mb-6 leading-relaxed">
            Saat ini belum ada berita yang dipublikasikan. Silakan cek kembali nanti untuk mendapatkan informasi terbaru.
          </p>
                    
          <div class="flex justify-center gap-2 opacity-30">
            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
          </div>
        </div>
                
        <div class="absolute inset-0 -z-10 overflow-hidden">
          <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-blue-100 rounded-full opacity-20 animate-pulse"></div>
          <div class="absolute bottom-1/4 right-1/4 w-24 h-24 bg-purple-100 rounded-full opacity-20 animate-pulse" style="animation-delay: 1s"></div>
        </div>
      </div>
    </div>
    
    <div v-else class="flex justify-center px-5 py-10">
      <div
        class="w-full max-w-4xl bg-white/60 backdrop-blur-md rounded-2xl shadow-lg border border-gray-200 p-6"
      >
        <button
          @click="selectedBerita = null"
          class="mb-4 text-blue-600 hover:underline text-sm flex items-center gap-1"
        >
          <PhCaretLeft :size="16" /> Kembali ke Daftar Berita
        </button>

        <h1 class="text-3xl font-bold mb-4">{{ selectedBerita.judul }}</h1>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
          <img
            src="https://ui-avatars.com/api/?name=Admin&background=random"
            class="w-6 h-6 rounded-full"
            :alt="selectedBerita.user?.name || 'Admin'"
          />
          <span>{{ selectedBerita.dibuat_oleh }}</span>
          <span>•</span>
          <span>{{ formatTanggal(selectedBerita.updated_at) }}</span>
          <span
            class="ml-auto bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-xs font-semibold"
          >
            {{ selectedBerita.kategori }}
          </span>
        </div>

        <img
          :src="getThumbnailUrl(selectedBerita.thumbnail)"
          @error="$event.target.src = PLACEHOLDER"
          class="w-full h-100 object-cover mb-6"
          :alt="selectedBerita.slug || 'Gambar Berita'"
        />

        <div class="prose max-w-none" v-html="selectedBerita.konten"></div>
        <hr class="my-6 border-gray-300" />
        
        <div class="mb-8">
          <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            {{ replyTo ? "Balas Komentar" : "Tinggalkan Komentar" }}
          </h2>
                    
          <div v-if="replyTo" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
              <span class="text-sm text-blue-800 flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                {{ replyTo.isMention ? `Membalas @${replyTo.name}` : `Membalas komentar dari ${replyTo.name}` }}
              </span>
              <button
                @click="replyTo = null"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 hover:bg-blue-100 px-2 py-1 rounded transition-colors"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Batal
              </button>
            </div>
          </div>
          
          <div class="flex gap-4 items-end">
            <div class="flex-shrink-0">
              <img
                :src="`https://ui-avatars.com/api/?name=${auth.user?.name || 'User'}&background=random`"
                class="w-10 h-10 rounded-full ring-2 ring-blue-100"
              />
            </div>
            
            <div class="flex-1">
              <textarea
                v-model="comment"
                class="w-full min-h-[80px] p-4 border border-gray-200 rounded-2xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400 bg-gray-50 focus:bg-white transition-colors"
                placeholder="Bagikan pendapat Anda..."
                rows="3"
                @input="handleCommentInput"
                @keydown.ctrl.enter="submitComment"
              ></textarea>
              
              <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-gray-400">Tekan Ctrl+Enter untuk mengirim</span>
                <button
                  type="button"
                  @click="submitComment"
                  :disabled="!comment.trim() || loading"
                  class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-full hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <svg v-if="loading" class="w-4 h-4 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 12a8 8 0 018-8V2.5"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                  </svg>
                  {{ loading ? 'Mengirim...' : 'Kirim' }}
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <span>Komentar</span>
            <span class="text-sm font-normal text-gray-500">({{ comments.length }})</span>
          </h3>
          
          <div v-if="comments.length" class="space-y-6">
            <template v-for="komentar in comments" :key="komentar.komentar_berita_id">
              <div class="relative">
                <!-- MAIN COMMENT -->
                <div class="flex gap-4">
                  <div class="relative flex-shrink-0">
                    <img
                      :src="`https://ui-avatars.com/api/?name=${komentar.user.name}&background=random`"
                      class="w-10 h-10 rounded-full ring-2 ring-white shadow-sm"
                    />                    
                    <div 
                      v-if="komentar.replies?.length && showReplies[komentar.komentar_berita_id]"
                      class="absolute top-12 left-5 w-0.5 bg-gradient-to-b from-blue-200 to-transparent h-full"
                    ></div>
                  </div>
                  
                  <div class="flex-1 bg-gray-50 rounded-2xl p-4 relative">                    
                    <div class="absolute -left-2 top-4 w-0 h-0 border-t-[8px] border-b-[8px] border-r-[8px] border-t-transparent border-b-transparent border-r-gray-50"></div>
                    
                    <div class="flex items-start justify-between mb-2">
                      <div>
                        <span class="font-semibold text-gray-900">{{ komentar.user.name }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ formatTanggal(komentar.created_at) }}</span>
                      </div>
                                            
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                        <button 
                          v-if="komentar.user.user_id === currentUserId"
                          @click="deleteComment(komentar.komentar_berita_id)"
                          class="text-gray-400 hover:text-red-500 p-1 rounded"
                          title="Hapus komentar"
                        >
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                    
                    <p class="text-gray-700 text-sm leading-relaxed mb-3">{{ komentar.komentar }}</p>

                    <div class="flex items-center justify-between">
                      <button 
                        @click="startReply(komentar, false)" 
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 hover:bg-blue-50 px-2 py-1 rounded-full transition-colors"
                      >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Balas
                      </button>
                                            
                      <button 
                        v-if="komentar.replies?.length"
                        @click="toggleReplies(komentar.komentar_berita_id)"
                        class="text-xs text-gray-600 hover:text-gray-800 font-medium flex items-center gap-1 hover:bg-gray-100 px-2 py-1 rounded-full transition-colors"
                      >
                        <svg 
                          class="w-3 h-3 transform transition-transform" 
                          :class="{ 'rotate-180': showReplies[komentar.komentar_berita_id] }"
                          fill="currentColor" 
                          viewBox="0 0 20 20"
                        >
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        {{ showReplies[komentar.komentar_berita_id] ? 'Sembunyikan' : 'Tampilkan' }} balasan ({{ komentar.replies.length }})
                      </button>
                    </div>
                  </div>
                </div>
                
                <div 
                  v-if="komentar.replies?.length && showReplies[komentar.komentar_berita_id]"
                  class="mt-4 space-y-4 ml-14 relative"
                >
                  <div 
                    v-for="(reply, index) in komentar.replies" 
                    :key="reply.komentar_berita_id" 
                    class="flex gap-3 relative group"
                  >                   
                    <div class="absolute -left-14 top-5 w-10 h-0.5 bg-blue-200"></div>
                    <div v-if="index === 0" class="absolute -left-14 -top-4 w-0.5 h-9 bg-blue-200"></div>
                    
                    <div class="flex-shrink-0">
                      <img
                        :src="`https://ui-avatars.com/api/?name=${reply.user.name}&background=random`"
                        class="w-8 h-8 rounded-full ring-2 ring-white shadow-sm"
                      />
                    </div>
                    
                    <div class="flex-1 bg-white rounded-xl p-3 border border-gray-100 shadow-sm relative">                      
                      <div class="absolute -left-2 top-3 w-0 h-0 border-t-[6px] border-b-[6px] border-r-[6px] border-t-transparent border-b-transparent border-r-white"></div>
                      
                      <div class="flex items-start justify-between mb-2">
                        <div>
                          <span class="font-medium text-gray-900 text-sm">{{ reply.user.name }}</span>
                          <span class="text-xs text-gray-500 ml-2">{{ formatTanggal(reply.created_at) }}</span>
                        </div>
                        
                        <button 
                          v-if="reply.user.user_id === currentUserId"
                          @click="deleteComment(reply.komentar_berita_id)"
                          class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 p-1 rounded transition-opacity"
                          title="Hapus balasan"
                        >
                          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                          </svg>
                        </button>
                      </div>
                      
                      <p class="text-gray-700 text-sm leading-relaxed mb-2">{{ reply.komentar }}</p>
                      
                      <button 
                        @click="startReply(reply, true)" 
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 hover:bg-blue-50 px-2 py-1 rounded-full transition-colors"
                      >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Balas
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
          
          <div v-else class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import Api from "@/services/Api";
import { useAuthStore } from "@/stores/auth";
import { useMessage } from "naive-ui"
import { PhCalendarBlank, PhCaretRight, PhCaretLeft } from "@phosphor-icons/vue";
import dayjs from "dayjs";
import "dayjs/locale/id";

const auth = useAuthStore();
const message = useMessage() 
const loading = ref(false) 
const baseUrl = import.meta.env.VITE_API_BASE_URL;
const PLACEHOLDER =
  "https://media.istockphoto.com/id/1180410208/vector/landscape-image-gallery-with-the-photos-stack-up.jpg?s=612x612";
const beritaList = ref([]);
const selectedBerita = ref(null);
const comment = ref("");
const comments = ref([]);
const replyTo = ref(null);
const showReplies = ref({});

const toggleReplies = (commentId) => {
  showReplies.value[commentId] = !showReplies.value[commentId];
};

const initializeRepliesVisibility = (comments) => {
  comments.forEach(comment => {
    if (comment.replies?.length && showReplies.value[comment.komentar_berita_id] === undefined) {
      showReplies.value[comment.komentar_berita_id] = false; 
    }
  });
};

const currentUserId = computed(() => {
  return auth.user?.user_id || auth.user?.id || null;
});
const handleCommentInput = (event) => {
  const value = event.target ? event.target.value : event;
  console.log("📝 Comment input changed:", value);
  comment.value = value;
};

const submitComment = async () => {
  
  if (!comment.value || !comment.value.trim()) {
    message.warning("Komentar tidak boleh kosong");
    return;
  }

  if (!selectedBerita.value) {
    message.error("Berita tidak ditemukan");
    return;
  }

  if (!currentUserId.value) {
    message.error("Anda harus login terlebih dahulu");
    return;
  }

  loading.value = true;

  try {
    // Determine parent_id logic:
    // - If replying to main comment: use that comment's ID as parent_id
    // - If replying to a reply: use the MAIN comment's ID as parent_id (flatten structure)
    let parentId = null;
    let commentText = comment.value.trim();

    if (replyTo.value) {      
      const mainParent = findMainParentComment(replyTo.value.id);
      parentId = mainParent ? mainParent.komentar_berita_id : replyTo.value.id;
      
      if (replyTo.value.isMention) {
        commentText = `@${replyTo.value.name} ${commentText}`;
      }
    }

    const payload = {
      daftar_berita_id: selectedBerita.value.daftar_berita_id,
      komentar: commentText,
      parent_id: parentId,
    };

    const res = await Api.post("/komentar", payload);
  
    if (parentId) {

      const mainParent = findCommentById(parentId);
      if (mainParent) {
        if (!mainParent.replies) mainParent.replies = [];
        mainParent.replies.push(res.data.data); 
        
        showReplies.value[mainParent.komentar_berita_id] = true;
      }
    } else {      
      comments.value.unshift(res.data.data);
    }
    
    comment.value = "";
    replyTo.value = null;
    message.success("Komentar berhasil ditambahkan ✅");
    
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message || "Gagal mengirim komentar";
    message.error(`Gagal mengirim komentar: ${errorMessage}`);
  } finally {
    loading.value = false;
  }
};

const findMainParentComment = (commentId) => {

  const mainComment = comments.value.find((c) => c.komentar_berita_id === commentId);
  if (mainComment) {
    return mainComment; 
  }
  
  for (let c of comments.value) {
    if (c.replies?.length) {
      const reply = c.replies.find((r) => r.komentar_berita_id === commentId);
      if (reply) {
        return c; 
      }
    }
  }
  return null;
};

const findCommentById = (id) => {
  let found = comments.value.find((c) => c.komentar_berita_id === id);
  if (found) return found;
  for (let c of comments.value) {
    if (c.replies?.length) {
      found = c.replies.find((r) => r.komentar_berita_id === id);
      if (found) return found;
    }
  }
  return null;
};

const startReply = (comment, isReplyToReply = false) => {
  replyTo.value = {
    id: comment.komentar_berita_id,
    name: comment.user.name,
    isMention: isReplyToReply 
  };
    
  setTimeout(() => {
    const commentForm = document.querySelector('textarea[placeholder="Bagikan pendapat Anda..."]');
    if (commentForm) {
      commentForm.focus();
      commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }, 100);
};

const deleteComment = async (id) => {
  try {
    await Api.delete(`/komentar/${id}`);
    removeCommentFromList(id);
    message.success("Komentar berhasil dihapus");
  } catch (error) {
    message.error("Gagal menghapus komentar");
  }
};

const removeCommentFromList = (id) => {
  const removeFrom = (list) => {
    const idx = list.findIndex((c) => c.komentar_berita_id === id);
    if (idx !== -1) return list.splice(idx, 1);
    for (let c of list) {
      if (c.replies?.length) removeFrom(c.replies);
    }
  };
  removeFrom(comments.value);
};

const formatTanggal = (tgl) => {
  return dayjs(tgl).locale("id").format("D MMMM YYYY | HH:mm");
};

const getThumbnailUrl = (path) => {
  if (!path || path === "null" || path === "undefined" || path.trim() === "") {
    return PLACEHOLDER;
  }
  return `${baseUrl}/storage/${path}`;
};

const fetchData = async () => {
  try {
    const res = await Api.get("/berita");
    beritaList.value = res.data.data;
  } catch (e) {
    console.error("Error fetching berita:", e);
  }
};

const fetchComments = async (beritaId) => {
  try {
    const response = await Api.get(`/komentar/${beritaId}`);
    comments.value = response.data.data;
        
    initializeRepliesVisibility(comments.value);
  } catch (error) {
    console.error("Error fetching comments:", error);
  }
};

const lihatDetail = async (berita) => {
  selectedBerita.value = berita;
  await fetchComments(berita.daftar_berita_id);
};

onMounted(() => {
  fetchData();
});
</script>