"use client";

import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Card, Field, Input, Stack, Flex } from "@chakra-ui/react";
import { TicketLabel } from "./TicketLabel";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "./ui/toaster";

export const LoginCard = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async () => {
    if (!email || !password) {
      toaster.error({
        title: "Erreur",
        description: "Veuillez remplir tous les champs",
      });
      return;
    }

    setIsLoading(true);
    try {
      await login(email, password);
      toaster.success({
        title: "Connexion réussie",
        description: "Bienvenue !",
      });
      navigate("/");
    } catch (error) {
      toaster.error({
        title: "Erreur de connexion",
        description: error instanceof Error ? error.message : "Email ou mot de passe incorrect",
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleSubmit();
    }
  };

  return (
    <Card.Root
      maxW="sm"
      bg="yellow.500"
      color="black"
      borderRadius="lg"
      p="4"
    >
      <Card.Header>
        <Card.Title textAlign="center" textTransform="uppercase">
          Se connecter
        </Card.Title>
      </Card.Header>

      <Card.Body>
        <Stack gap="4" w="full">
          <Field.Root>
            <Field.Label>Email</Field.Label>
            <Input
              type="email"
              bg="white"
              color="black"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              onKeyPress={handleKeyPress}
              disabled={isLoading}
            />
          </Field.Root>

          <Field.Root>
            <Field.Label>Mot de passe</Field.Label>
            <Input
              type="password"
              bg="white"
              color="black"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              onKeyPress={handleKeyPress}
              disabled={isLoading}
            />
          </Field.Root>
        </Stack>
      </Card.Body>

      <Card.Footer justifyContent="center">
        <Flex gap="4">
          <TicketLabel text="Créer son compte" to="/register" />
          <TicketLabel
            text={isLoading ? "Connexion..." : "Se connecter"}
            onClick={handleSubmit}
          />
        </Flex>
      </Card.Footer>
    </Card.Root>
  );
};
