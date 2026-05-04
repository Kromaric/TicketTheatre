import { useEffect, useState } from "react";
import {
  Box,
  Button,
  Card,
  Heading,
  Table,
  Spinner,
  Center,
  Input,
  Flex,
  Stack,
  Text,
} from "@chakra-ui/react";
import { coreService, type Category } from "../../services/core.service";
import { toaster } from "../../components/ui/toaster";
import { useAuth } from "../../contexts/AuthContext";
import { useNavigate } from "react-router-dom";

export default function AdminCategories() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState({ name: "", description: "" });
  const [creating, setCreating] = useState(false);

  useEffect(() => {
    if (!user || user.role !== "admin") {
      navigate("/");
      return;
    }
    loadCategories();
  }, [user]);

  const loadCategories = async () => {
    try {
      const data = await coreService.getCategories();
      setCategories(data);
    } catch (error) {
      toaster.error({
        title: "Erreur",
        description: "Impossible de charger les catégories",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    if (!formData.name.trim()) {
      toaster.error({ title: "Erreur", description: "Le nom est requis" });
      return;
    }

    try {
      await coreService.createCategory(formData);
      toaster.success({ title: "Succès", description: "Catégorie créée" });
      setFormData({ name: "", description: "" });
      setCreating(false);
      loadCategories();
    } catch (error) {
      toaster.error({
        title: "Erreur",
        description: "Impossible de créer la catégorie",
      });
    }
  };

  const handleUpdate = async (id: number) => {
    try {
      await coreService.updateCategory(id, formData);
      toaster.success({
        title: "Succès",
        description: "Catégorie mise à jour",
      });
      setEditingId(null);
      setFormData({ name: "", description: "" });
      loadCategories();
    } catch (error) {
      toaster.error({
        title: "Erreur",
        description: "Impossible de mettre à jour",
      });
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm("Supprimer cette catégorie ?")) return;

    try {
      await coreService.deleteCategory(id);
      toaster.success({ title: "Succès", description: "Catégorie supprimée" });
      loadCategories();
    } catch (error) {
      toaster.error({
        title: "Erreur",
        description: "Impossible de supprimer",
      });
    }
  };

  const startEdit = (category: Category) => {
    setEditingId(category.id);
    setFormData({
      name: category.name,
      description: category.description || "",
    });
    setCreating(false);
  };

  if (loading) {
    return (
      <Center minH="60vh">
        <Spinner size="xl" color="red.500" />
      </Center>
    );
  }

  return (
    <Box w="full" maxW="1200px" mx="auto" py={8} px={4}>
      <Flex justify="space-between" align="center" mb={6}>
        <Heading size="xl">Gestion des Catégories</Heading>
        <Button
          colorScheme="red"
          onClick={() => {
            setCreating(true);
            setEditingId(null);
            setFormData({ name: "", description: "" });
          }}
        >
          + Nouvelle catégorie
        </Button>
      </Flex>

      {creating && (
        <Card.Root bg="gray.800" mb={6}>
          <Card.Body>
            <Stack gap={4}>
              <Heading size="md">Nouvelle catégorie</Heading>
              <Input
                placeholder="Nom"
                value={formData.name}
                onChange={(e) =>
                  setFormData({ ...formData, name: e.target.value })
                }
              />
              <Input
                placeholder="Description"
                value={formData.description}
                onChange={(e) =>
                  setFormData({ ...formData, description: e.target.value })
                }
              />
              <Flex gap={2}>
                <Button colorScheme="green" onClick={handleCreate}>
                  Créer
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setCreating(false);
                    setFormData({ name: "", description: "" });
                  }}
                >
                  Annuler
                </Button>
              </Flex>
            </Stack>
          </Card.Body>
        </Card.Root>
      )}

      <Card.Root bg="gray.800">
        <Card.Body>
          <Table.Root>
            <Table.Header>
              <Table.Row>
                <Table.ColumnHeader>ID</Table.ColumnHeader>
                <Table.ColumnHeader>Nom</Table.ColumnHeader>
                <Table.ColumnHeader>Description</Table.ColumnHeader>
                <Table.ColumnHeader>Actions</Table.ColumnHeader>
              </Table.Row>
            </Table.Header>
            <Table.Body>
              {categories.map((cat) => (
                <Table.Row key={cat.id}>
                  <Table.Cell>{cat.id}</Table.Cell>
                  <Table.Cell>
                    {editingId === cat.id ? (
                      <Input
                        size="sm"
                        value={formData.name}
                        onChange={(e) =>
                          setFormData({ ...formData, name: e.target.value })
                        }
                      />
                    ) : (
                      cat.name
                    )}
                  </Table.Cell>
                  <Table.Cell>
                    {editingId === cat.id ? (
                      <Input
                        size="sm"
                        value={formData.description}
                        onChange={(e) =>
                          setFormData({
                            ...formData,
                            description: e.target.value,
                          })
                        }
                      />
                    ) : (
                      cat.description || "-"
                    )}
                  </Table.Cell>
                  <Table.Cell>
                    {editingId === cat.id ? (
                      <Flex gap={2}>
                        <Button
                          size="sm"
                          colorScheme="green"
                          onClick={() => handleUpdate(cat.id)}
                        >
                          Sauver
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => {
                            setEditingId(null);
                            setFormData({ name: "", description: "" });
                          }}
                        >
                          Annuler
                        </Button>
                      </Flex>
                    ) : (
                      <Flex gap={2}>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => startEdit(cat)}
                        >
                          Éditer
                        </Button>
                        <Button
                          size="sm"
                          colorScheme="red"
                          onClick={() => handleDelete(cat.id)}
                        >
                          Supprimer
                        </Button>
                      </Flex>
                    )}
                  </Table.Cell>
                </Table.Row>
              ))}
            </Table.Body>
          </Table.Root>
          {categories.length === 0 && (
            <Center py={8}>
              <Text color="gray.400">Aucune catégorie</Text>
            </Center>
          )}
        </Card.Body>
      </Card.Root>
    </Box>
  );
}
