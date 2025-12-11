"use client";

import { Card, Field, Input, Stack, Flex } from "@chakra-ui/react";
import { TicketLabel } from "./TicketLabel";

export const LoginCard = () => {
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
            <Input type="email" bg="white" color="black" />
          </Field.Root>

          <Field.Root>
            <Field.Label>Mot de passe</Field.Label>
            <Input type="password" bg="white" color="black" />
          </Field.Root>
        </Stack>
      </Card.Body>

      <Card.Footer justifyContent="center">
        <Flex gap="4">
          <TicketLabel text="Créer son compte" />
          <TicketLabel text="Se connecter" />
        </Flex>
      </Card.Footer>
    </Card.Root>
  );
};
