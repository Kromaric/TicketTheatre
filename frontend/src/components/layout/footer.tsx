import { Box, Flex, Text, Input } from "@chakra-ui/react";

export default function Footer() {
  return (
    <Box bg="red.800" p="6" mb="2" w="full">
      <Flex
        alignItems="center"
        gap="4"
        flexDirection="column"
        w="full"
        sm={{ flexDirection: "row", justifyContent: "space-around" }}
      >
        <Flex alignItems="center" flexDirection="column">
          <Text textStyle="xl">NOUS TROUVER</Text>
          <Text textStyle="sm">Le Velum</Text>
          <Text textStyle="sm">3 place de la sirène</Text>
          <Text textStyle="sm">44100 Nantes</Text>
        </Flex>
        <Flex alignItems="center" flexDirection="column">
          <Text textStyle="xl">NEWSLETTER</Text>
          <Input
            placeholder="Votre email"
            bg="white"
            borderRadius="md"
            size="xs"
          />
        </Flex>
        <Flex alignItems="center" flexDirection="column">
          <Text textStyle="xl">NOUS CONTACTER</Text>
          <Text textStyle="sm">02 43 76 60 36</Text>
          <Text textStyle="sm">velumnantes@gmail.com</Text>
        </Flex>
      </Flex>
    </Box>
  );
}
