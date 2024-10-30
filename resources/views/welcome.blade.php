<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD con Vue.js y Vuetify</title>

    <link href="https://cdn.jsdelivr.net/npm/vuetify@3.7.3/dist/vuetify.min.css" rel="stylesheet">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }

        .form-container {
            flex: 1;
            margin-right: 20px;
        }

        .table-container {
            flex: 1;
        }
    </style>
</head>

<body>
    <div id="app">
        <v-app>
            <div class="container">
                <div class="form-container">
                    <v-card>
                        <v-card-title>
                            <h2>@{{ editing ? 'Editar candidato' : 'Agregar candidato' }}</h2>
                        </v-card-title>
                        <v-card-text>
                            <v-form ref="form" v-model="valid">
                                <v-text-field v-model="candidate.name" label="Nombre" required
                                    variant="outlined"></v-text-field>
                                <v-text-field v-model="candidate.phone" label="Teléfono" required
                                    variant="outlined"></v-text-field>
                                <v-text-field v-model="candidate.profession" label="Ocupación" required
                                    variant="outlined"></v-text-field>
                                <v-btn @click="editing ? updateCandidate() : saveCandidate()"
                                    color="primary">@{{ editing ? 'Actualizar' : 'Guardar' }}</v-btn>
                                <v-alert class="mt-2" v-if="message" :type="isSuccess ? 'success' : 'error'"
                                    dismissible>
                                    @{{ message }}
                                </v-alert>
                            </v-form>
                        </v-card-text>
                    </v-card>
                </div>

                <div class="table-container">
                    <v-card>
                        <v-card-title>
                            <h2>Lista de candidatos</h2>
                        </v-card-title>
                        <v-card-text>
                            <v-data-table :headers="headers" :items="candidates">
                                <template v-slot:item.actions="{ item }">
                                    <v-btn @click="editCandidate(item)" color="yellow" class="mx-1">Editar</v-btn>
                                    <v-btn @click="deletePerson(item.id)" color="red" class="mx-1">Eliminar</v-btn>
                                </template>
                            </v-data-table>
                        </v-card-text>
                    </v-card>
                </div>
            </div>
        </v-app>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@3.7.3/dist/vuetify.min.js"></script>
    <script>
        const {
            createApp,
            ref
        } = Vue;

        const {
            createVuetify
        } = Vuetify;

        const vuetify = createVuetify();

        createApp({
            setup() {
                const candidate = ref({
                    name: '',
                    phone: '',
                    profession: ''
                });
                const candidates = ref([]);
                const message = ref('');
                const isSuccess = ref(true);
                const valid = ref(false);
                const headers = [{
                        text: 'Nombre',
                        value: 'name'
                    },
                    {
                        text: 'Teléfono',
                        value: 'phone'
                    },
                    {
                        text: 'Ocupación',
                        value: 'profession'
                    },
                    {
                        text: 'Acciones',
                        value: 'actions',
                        sortable: false
                    },
                ];
                const editing = ref(false);
                const currentCandidateId = ref(null);

                const fetchCandidates = async () => {
                    const response = await fetch('/api/candidates');
                    candidates.value = await response.json();
                };

                const saveCandidate = async () => {
                    const response = await fetch('/api/candidates', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(candidate.value)
                    });

                    if (response.ok) {
                        message.value = 'Datos guardados exitosamente!';
                        isSuccess.value = true;
                        resetForm();
                    } else {
                        const errors = await response.json();
                        message.value = 'Error al guardar: ' + Object.values(errors.errors).map(e => e[0])
                            .join(', ');
                        isSuccess.value = false;
                    }
                    fetchCandidates();
                };

                const deletePerson = async (id) => {
                    if (confirm('¿Estás seguro de que deseas eliminar esta candidata?')) {
                        await fetch(`/api/candidates/${id}`, {
                            method: 'DELETE',
                        });
                        fetchCandidates(); // Actualiza la lista después de eliminar
                    }
                };

                const editCandidate = (item) => {
                    candidate.value = {
                        ...item
                    }; // Llena el formulario con los datos del candidato
                    editing.value = true; // Cambia el estado a editar
                    currentCandidateId.value = item.id; // Guarda el ID del candidato actual
                };

                const updateCandidate = async () => {
                    const response = await fetch(`/api/candidates/${currentCandidateId.value}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(candidate.value)
                    });

                    if (response.ok) {
                        message.value = 'Candidata actualizada exitosamente!';
                        isSuccess.value = true;
                        resetForm();
                    } else {
                        const errors = await response.json();
                        message.value = 'Error al actualizar: ' + Object.values(errors.errors).map(e => e[
                            0]).join(', ');
                        isSuccess.value = false;
                    }
                    fetchCandidates();
                };

                const resetForm = () => {
                    candidate.value = {
                        name: '',
                        phone: '',
                        profession: ''
                    }; // Reiniciar el formulario
                    editing.value = false; // Regresar a agregar
                };

                // Cargar candidatos al iniciar
                fetchCandidates();

                return {
                    candidate,
                    candidates,
                    message,
                    isSuccess,
                    valid,
                    headers,
                    editing,
                    saveCandidate,
                    deletePerson,
                    editCandidate,
                    updateCandidate
                };
            }
        }).use(vuetify).mount('#app');
    </script>

</body>

</html>
