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

  /**
   * Met à jour les champs du formulaire.
   */
  const handleChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  /**
   * Gère la soumission du formulaire.
   */
  const handleSubmit = async () => {
    // 1. Validation locale des champs requis
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

    setIsLoading(true);

    try {
      // 2. Envoi direct au Core Service via le contexte Auth
      // Comme spécifié, l'API attend bien les deux champs 'first_name' et 'last_name'
      await register({
        first_name: formData.first_name.trim(),
        last_name: formData.last_name.trim(),
        email: formData.email,
        password: formData.password,
        password_confirmation: formData.password_confirmation
      });

      toaster.success({
        title: "Inscription réussie",
        description: "Ton compte a été créé avec succès !",
      });

      // 3. Redirection vers la page de connexion
      navigate("/login");

    } catch (error) {
      toaster.error({
        title: "Erreur lors de l'inscription",
        description: error instanceof Error ? error.message : "Une erreur est survenue lors de l'inscription",
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
                _placeholder={{ color: "gray.500" }}
                value={formData.last_name}
                onChange={(e) => handleChange('last_name', e.target.value)}
                disabled={isLoading}
            />
            <Input
                placeholder="Prénom *"
                bg="white"
                color="black"
                size="sm"
                _placeholder={{ color: "gray.500" }}
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
                _placeholder={{ color: "gray.500" }}
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
                _placeholder={{ color: "gray.500" }}
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
                _placeholder={{ color: "gray.500" }}
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