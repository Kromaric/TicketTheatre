import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Card, Input, Stack } from "@chakra-ui/react";
import { TicketLabel } from "./TicketLabel";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "./ui/toaster";

const isValidEmail = (email: string) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(email.trim());

export const SignupCard = () => {
  const [formData, setFormData] = useState({
    first_name: "",
    last_name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });
  const [isLoading, setIsLoading] = useState(false);
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async () => {
    // Validation
    if (!formData.first_name || !formData.last_name || !formData.email || !formData.password) {
      toaster.error({
        title: "Erreur",
        description: "Veuillez remplir tous les champs obligatoires",
      });
      return;
    }

    if (!isValidEmail(formData.email)) {
      toaster.error({
        title: "Erreur",
        description: "Veuillez saisir une adresse email valide",
      });
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      toaster.error({
        title: "Erreur",
        description: "Les mots de passe ne correspondent pas",
      });
      return;
    }

    if (formData.password.length < 8) {
      toaster.error({
        title: "Erreur",
        description: "Le mot de passe doit contenir au moins 8 caractères",
      });
      return;
    }

    setIsLoading(true);
    try {
      await register(formData);
      toaster.success({
        title: "Inscription réussie",
        description: "Bienvenue sur TicketTheatre !",
      });
      navigate("/");
    } catch (error) {
      toaster.error({
        title: "Erreur lors de l'inscription",
        description: error instanceof Error ? error.message : "Une erreur est survenue",
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Card.Root
      w="full"
      maxW="420px"
      bg="yellow.500"
      color="black"
      borderRadius="lg"
      p="5"
    >
      <Card.Header pb="2">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          fontSize="md"
        >
          Créer son compte
        </Card.Title>
      </Card.Header>

      <Card.Body pt="2" pb="3">
        <Stack gap="3" w="full">
          <Input
            placeholder="Nom *"
            bg="white"
            color="black"
            size="sm"
            value={formData.last_name}
            onChange={(e) => handleChange('last_name', e.target.value)}
            disabled={isLoading}
          />
          <Input
            placeholder="Prénom *"
            bg="white"
            color="black"
            size="sm"
            value={formData.first_name}
            onChange={(e) => handleChange('first_name', e.target.value)}
            disabled={isLoading}
          />
          <Input
            type="email"
            placeholder="Email *"
            bg="white"
            color="black"
            size="sm"
            value={formData.email}
            onChange={(e) => handleChange('email', e.target.value)}
            disabled={isLoading}
          />
          <Input
            type="password"
            placeholder="Mot de passe (min. 8 caractères) *"
            bg="white"
            color="black"
            size="sm"
            value={formData.password}
            onChange={(e) => handleChange('password', e.target.value)}
            disabled={isLoading}
          />
          <Input
            type="password"
            placeholder="Confirmation mot de passe *"
            bg="white"
            color="black"
            size="sm"
            value={formData.password_confirmation}
            onChange={(e) => handleChange('password_confirmation', e.target.value)}
            disabled={isLoading}
          />
        </Stack>
      </Card.Body>

      <Card.Footer justifyContent="center" pt="2">
        <TicketLabel
          text={isLoading ? "Inscription..." : "Créer son compte"}
          onClick={handleSubmit}
        />
      </Card.Footer>
    </Card.Root>
  );
};
