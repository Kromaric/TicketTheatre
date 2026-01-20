import { Card, Input, Stack, Select, createListCollection } from "@chakra-ui/react";
import { TicketLabel } from "./TicketLabel";

const sexeCollection = createListCollection({
  items: [
    { label: "Masculin", value: "masculin" },
    { label: "Féminin", value: "feminin" },
  ],
});

export const SignupCard = () => {
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
          <Input placeholder="Nom" bg="white" color="black" size="sm" />
          <Input placeholder="Prénom" bg="white" color="black" size="sm" />
          <Input type="email" placeholder="Email" bg="white" color="black" size="sm" />
          <Input type="tel" placeholder="Téléphone" bg="white" color="black" size="sm" />
          <Input type="date" bg="white" color="black" size="sm" />
          <Select.Root collection={sexeCollection} size="sm">
            <Select.Trigger bg="white" color="black">
              <Select.ValueText placeholder="Sexe" />
              <Select.Indicator />
            </Select.Trigger>

            <Select.Positioner>
              <Select.Content
                bg="white"
                color="black"
                borderWidth="1px"
                borderColor="blackAlpha.300"
                borderRadius="md"
                boxShadow="md"
                p="1"
                zIndex="2000"
              >
                {sexeCollection.items.map((item) => (
                  <Select.Item
                    key={item.value}
                    item={item}
                    px="3"
                    py="2"
                    borderRadius="sm"
                    _hover={{ bg: "yellow.100" }}
                    _highlighted={{ bg: "yellow.200" }}
                  >
                    <Select.ItemText>{item.label}</Select.ItemText>
                    <Select.ItemIndicator />
                  </Select.Item>
                ))}
              </Select.Content>
            </Select.Positioner>
          </Select.Root>

          <Input type="password" placeholder="Mot de passe" bg="white" color="black" size="sm" />
          <Input
            type="password"
            placeholder="Confirmation mot de passe"
            bg="white"
            color="black"
            size="sm"
          />
        </Stack>
      </Card.Body>

      <Card.Footer justifyContent="center" pt="2">
        <TicketLabel text="Créer son compte" />
      </Card.Footer>
    </Card.Root>
  );
};
